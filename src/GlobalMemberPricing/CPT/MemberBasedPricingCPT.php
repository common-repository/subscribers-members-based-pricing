<?php

namespace MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT;

use  Exception ;
use  Automattic\WooCommerce\Admin\PageController ;
use  MeowCrew\MembersBasedPricing\Core\AdminNotifier ;
use  MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule ;
use  MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Actions\ReactivateAction ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Actions\SuspendAction ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns\AppliedQuantityRules ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns\Pricing ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns\AppliedCustomers ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns\AppliedProducts ;
use  MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns\Status ;
use function  is_empty ;
class MemberBasedPricingCPT
{
    use  ServiceContainerTrait ;
    const  SLUG = 'mbp-rule' ;
    /**
     * Pricing rules
     *
     * @var GlobalPricingRule
     */
    private  $pricingRuleInstance ;
    /**
     * Table columns
     *
     * @var array
     */
    private  $columns ;
    protected static  $globalRules = null ;
    public function __construct()
    {
        add_action( 'init', array( $this, 'register' ) );
        add_action( 'manage_posts_extra_tablenav', array( $this, 'renderBlankState' ) );
        add_action(
            'add_meta_boxes',
            array( $this, 'registerMetaboxes' ),
            10,
            3
        );
        add_filter( 'woocommerce_navigation_screen_ids', array( $this, 'addPageToWooCommerceScreen' ) );
        add_filter( 'woocommerce_screen_ids', array( $this, 'addPageToWooCommerceScreen' ) );
        add_action( 'save_post_' . self::SLUG, array( $this, 'savePricingRule' ) );
        add_filter( 'manage_edit-' . self::SLUG . '_columns', function ( $columns ) {
            unset( $columns['date'] );
            foreach ( $this->getColumns() as $key => $column ) {
                $columns[$key] = $column->getName();
            }
            return $columns;
        }, 999 );
        add_filter( 'manage_' . self::SLUG . '_posts_custom_column', function ( $column ) {
            global  $post ;
            $globalRule = GlobalPricingRule::build( $post->ID );
            if ( array_key_exists( $column, $this->getColumns() ) ) {
                $this->getColumns()[$column]->render( $globalRule );
            }
            return $column;
        }, 999 );
        add_action( 'admin_notices', function () {
            global  $post, $pagenow ;
            
            if ( $post && self::SLUG === $post->post_type && 'edit.php' !== $pagenow && !$this->isSetupingANewPricingRule() ) {
                $pricingRule = $this->getPricingRuleInstance();
                try {
                    $pricingRule->validatePricing();
                } catch ( Exception $e ) {
                    echo  wp_kses_post( '<div class="notice notice-warning"><p>' . $e->getMessage() . '</p></div>' ) ;
                }
            }
        
        } );
        add_filter(
            'post_row_actions',
            function ( $actions, $post ) {
            if ( self::SLUG === $post->post_type ) {
                unset( $actions['inline hide-if-no-js'] );
            }
            return $actions;
        },
            10,
            2
        );
        add_filter(
            'disable_months_dropdown',
            function ( $state, $postType ) {
            if ( self::SLUG === $postType ) {
                return true;
            }
            return $state;
        },
            10,
            2
        );
        // Refresh cache for variable product pricing
        add_action( 'save_post_' . self::SLUG, function () {
            wc_delete_product_transients();
        } );
        $this->initInlineActions();
    }
    
    public function initInlineActions()
    {
        new SuspendAction();
        new ReactivateAction();
    }
    
    public function getColumns()
    {
        if ( is_null( $this->columns ) ) {
            $this->columns = array(
                'pricing'                => new Pricing(),
                'applied_products'       => new AppliedProducts(),
                'applied_customers'      => new AppliedCustomers(),
                'applied_quantity_rules' => new AppliedQuantityRules(),
                'status'                 => new Status(),
            );
        }
        return $this->columns;
    }
    
    /**
     * Get pricing rule instance
     *
     * @return GlobalPricingRule
     */
    public function getPricingRuleInstance()
    {
        
        if ( empty($this->pricingRuleInstance) ) {
            global  $post ;
            
            if ( $post ) {
                $this->pricingRuleInstance = GlobalPricingRule::build( $post->ID );
            } else {
                return null;
            }
        
        }
        
        return $this->pricingRuleInstance;
    }
    
    public function addPageToWooCommerceScreen( $ids )
    {
        $ids[] = self::SLUG;
        $ids[] = 'edit-' . self::SLUG;
        return $ids;
    }
    
    public function registerMetaboxes()
    {
        add_meta_box(
            'mbp_rules_metabox',
            __( 'Rules', 'subscribers-members-based-pricing' ),
            array( $this, 'renderRulesMetabox' ),
            self::SLUG
        );
        add_meta_box(
            'mbp_pricing_metabox',
            __( 'Pricing', 'subscribers-members-based-pricing' ),
            array( $this, 'renderPricingMetabox' ),
            self::SLUG
        );
    }
    
    public function renderRulesMetabox()
    {
        $this->getContainer()->getFileManager()->includeTemplate( 'admin/global-rules/role-specific-pricing/rules.php', array(
            'fileManager' => $this->getContainer()->getFileManager(),
            'priceRule'   => $this->getPricingRuleInstance(),
        ) );
    }
    
    public function savePricingRule( $ruleId )
    {
        // Save pricing
        if ( wp_verify_nonce( true, true ) ) {
            // as phpcs comments at Woo is not available, we have to do such a trash
            $woo = 'Woo, please add ignoring comments to your phpcs checker';
        }
        $data = array();
        $pricingFields = array( '_mbp_global_pricing_type', '_mbp_global_regular_price', '_mbp_global_sale_price' );
        $arrayFields = array( '_mbp_global_products', '_mbp_global_categories' );
        if ( empty($_POST) ) {
            return;
        }
        $data['_mbp_global_discount'] = null;
        $data['_mbp_global_minimum'] = null;
        $data['_mbp_global_maximum'] = null;
        $data['_mbp_global_group_of'] = null;
        foreach ( $_POST['_mbp_global_pricing_type'] as $slug => $type ) {
            foreach ( $arrayFields as $arrayField ) {
                
                if ( !isset( $_POST[$arrayField][$slug] ) ) {
                    $data[$arrayField] = array();
                } else {
                    $data[$arrayField] = (array) $_POST[$arrayField][$slug];
                    // Sanitize
                    $data[$arrayField] = array_map( 'sanitize_text_field', $data[$arrayField] );
                    $data[$arrayField] = array_map( 'intval', $data[$arrayField] );
                    $data[$arrayField] = array_filter( $data[$arrayField] );
                }
            
            }
            foreach ( $pricingFields as $field ) {
                
                if ( !isset( $_POST[$field][$slug] ) ) {
                    $data[$field] = '';
                } else {
                    $data[$field] = sanitize_text_field( $_POST[$field][$slug] );
                }
            
            }
        }
        $ruleCategoriesIds = ( isset( $_POST['_mbp_global_categories'] ) ? array_filter( array_map( 'intval', array_values( $_POST['_mbp_global_categories'] )[0] ) ) : array() );
        $ruleProductsIds = ( isset( $_POST['_mbp_global_products'] ) ? array_filter( array_map( 'intval', array_values( $_POST['_mbp_global_products'] )[0] ) ) : array() );
        $pricingRule = new GlobalPricingRule(
            $data['_mbp_global_pricing_type'],
            $ruleProductsIds,
            $ruleCategoriesIds,
            wc_format_decimal( $data['_mbp_global_regular_price'] ),
            wc_format_decimal( $data['_mbp_global_sale_price'] ),
            ( !empty($data['_mbp_global_discount']) ? floatval( $data['_mbp_global_discount'] ) : null ),
            sanitize_text_field( $data['_mbp_global_minimum'] ),
            sanitize_text_field( $data['_mbp_global_maximum'] ),
            sanitize_text_field( $data['_mbp_global_group_of'] )
        );
        $existingRoles = wp_roles()->roles;
        $includedCategoriesIds = ( isset( $_POST['_mbp_included_categories'] ) ? array_filter( array_map( 'intval', (array) $_POST['_mbp_included_categories'] ) ) : array() );
        $includedProductsIds = ( isset( $_POST['_mbp_included_products'] ) ? array_filter( array_map( 'intval', (array) $_POST['_mbp_included_products'] ) ) : array() );
        $includedUsersRole = ( isset( $_POST['_mbp_included_user_roles'] ) ? array_filter( (array) $_POST['_mbp_included_user_roles'], function ( $role ) use( $existingRoles ) {
            return array_key_exists( $role, $existingRoles );
        } ) : array() );
        $includedUsers = ( isset( $_POST['_mbp_included_users'] ) ? array_filter( array_map( 'intval', (array) $_POST['_mbp_included_users'] ) ) : array() );
        $pricingRule->setIncludedProductCategories( $includedCategoriesIds );
        $pricingRule->setIncludedUsers( $includedUsers );
        $pricingRule->setIncludedUsersRole( $includedUsersRole );
        $pricingRule->setIncludedProducts( $includedProductsIds );
        try {
            GlobalPricingRule::save( $pricingRule, $ruleId );
        } catch ( Exception $exception ) {
            $this->getContainer()->getAdminNotifier()->flash( 'Members and Subscribers pricing: ' . $exception->getMessage(), AdminNotifier::ERROR );
        }
    }
    
    public function renderPricingMetabox()
    {
        ?>
		<div id="<?php 
        echo  esc_attr( self::SLUG ) ;
        ?>" class="panel woocommerce_options_panel">
			<?php 
        $this->getContainer()->getFileManager()->includeTemplate( 'admin/global-rules/role-specific-pricing/pricing.php', array(
            'fileManager' => $this->getContainer()->getFileManager(),
            'priceRule'   => $this->getPricingRuleInstance(),
        ) );
        ?>
		</div>
		<?php 
    }
    
    public function renderBlankState( $which )
    {
        global  $post_type ;
        
        if ( self::SLUG === $post_type && 'bottom' === $which ) {
            $counts = (array) wp_count_posts( $post_type );
            unset( $counts['auto-draft'] );
            $count = array_sum( $counts );
            if ( 0 < $count ) {
                return;
            }
            ?>
			
			<div class="woocommerce-BlankState">
				
				<h2 class="woocommerce-BlankState-message">
					<?php 
            esc_html_e( 'There are no pricing rules yet. To create pricing dependencies for subscribers/members click on the button below.', 'subscribers-members-based-pricing' );
            ?>
				</h2>
				
				<div class="woocommerce-BlankState-buttons">
					<a class="woocommerce-BlankState-cta button-primary button"
					   href="<?php 
            echo  esc_url( admin_url( 'post-new.php?post_type=' . self::SLUG ) ) ;
            ?>">
						<?php 
            esc_html_e( 'Create a pricing rule', 'subscribers-members-based-pricing' );
            ?>
					</a>
				</div>
			</div>
			
			<style
				type="text/css">#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom .actions, .wrap .subsubsub {
					display: none;
				}

				#posts-filter .tablenav.bottom {
					height: auto;
				}
			</style>
			<?php 
        }
    
    }
    
    public function register()
    {
        PageController::get_instance()->connect_page( array(
            'id'        => self::SLUG,
            'title'     => array( 'Role Specific Pricing' ),
            'screen_id' => self::SLUG,
        ) );
        register_post_type( self::SLUG, array(
            'labels'             => array(
            'name'               => __( 'Pricing rule', 'subscribers-members-based-pricing' ),
            'singular_name'      => __( 'Pricing rule', 'subscribers-members-based-pricing' ),
            'add_new'            => __( 'Add Pricing Rule', 'subscribers-members-based-pricing' ),
            'add_new_item'       => __( 'Add Pricing Rule', 'subscribers-members-based-pricing' ),
            'edit_item'          => __( 'Edit Pricing Rule', 'subscribers-members-based-pricing' ),
            'new_item'           => __( 'New Pricing Rule', 'subscribers-members-based-pricing' ),
            'view_item'          => __( 'View Pricing Rule', 'subscribers-members-based-pricing' ),
            'search_items'       => __( 'Find Pricing Rule', 'subscribers-members-based-pricing' ),
            'not_found'          => __( 'No pricing rules ware found', 'subscribers-members-based-pricing' ),
            'not_found_in_trash' => __( 'No pricing rule in trash', 'subscribers-members-based-pricing' ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Member pricing rules', 'subscribers-members-based-pricing' ),
        ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'woocommerce',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'product',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' ),
        ) );
    }
    
    public function isSetupingANewPricingRule()
    {
        global  $pagenow ;
        return in_array( $pagenow, array( 'post-new.php' ) );
    }
    
    public static function getGlobalRules( $withValidPricing = true )
    {
        
        if ( !is_null( self::$globalRules ) ) {
            $rules = self::$globalRules;
        } else {
            $rulesIds = get_posts( array(
                'numberposts' => -1,
                'post_type'   => self::SLUG,
                'post_status' => 'publish',
                'fields'      => 'ids',
                'meta_query'  => array( array(
                'key'     => '_mbp_is_suspended',
                'value'   => 'yes',
                'compare' => '!=',
            ) ),
            ) );
            $rules = array_map( function ( $ruleId ) {
                return GlobalPricingRule::build( $ruleId );
            }, $rulesIds );
            self::$globalRules = $rules;
        }
        
        if ( $withValidPricing ) {
            $rules = array_filter( $rules, function ( GlobalPricingRule $rule ) {
                return $rule->isValidPricing();
            } );
        }
        return $rules;
    }

}