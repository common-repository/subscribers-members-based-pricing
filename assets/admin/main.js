jQuery(document).ready(function ($) {

    var PricingRuleBlock = function () {

        this.$block = null;
        this.initializedBlocks = [];

        this.init = function (id) {

            this.variationCanBeChangedAlreadyTriggered = false;
            this.id = id;
            this.$block = jQuery('#' + id);

            if ($.selectWoo) {
                this.$block.find('.mbp-add-new-rule-form__identifier-selector').selectWoo();
            }

            if (this.initializedBlocks[id] !== undefined) {
                this.unbindEvents();
            }

            this.bindEvents();

            this.initializedBlocks[id] = this;
        };

        this.bindEvents = function () {
            $('body').on('click', '#' + this.id + ' .mbp-pricing-rule-action--delete', this.removeRole.bind(this));
            $('body').on('click', '#' + this.id + ' .mbp-pricing-rule__header', this.toggleRoleView.bind(this));
            $('body').on('click', '#' + this.id + ' .mbp-add-new-rule-form__add-button', this.addRole.bind(this));

            $('body').on('change', '#' + this.id + ' .mbp-pricing-type-input', this.handlePricingTypeView.bind(this)).trigger('change');
        }

        this.unbindEvents = function () {
            $('body').off('click', '#' + this.id + ' .mbp-pricing-rule-action--delete');
            $('body').off('click', '#' + this.id + ' .mbp-pricing-rule__header');
            $('body').off('click', '#' + this.id + ' .mbp-add-new-rule-form__add-button');

            $('body').off('change', '#' + this.id + ' .mbp-pricing-type-input');
        }

        this.handlePricingTypeView = function (event) {

            var container = $(event.target).closest('.mbp-pricing-rule-form');

            if (container.length) {

                var pricingType = container.find('.mbp-pricing-type-input')
                    .filter(':checked')
                    .val();

                if (pricingType === 'flat') {
                    container.find('.mbp-pricing-rule-form__flat_prices').show();
                    container.find('.mbp-pricing-rule-form__percentage_discount').hide();
                } else {
                    container.find('.mbp-pricing-rule-form__flat_prices').hide();
                    container.find('.mbp-pricing-rule-form__percentage_discount').show();
                }
            }

        }

        this.getPricingType = function () {
            return this.$block.find('#' + this.id + ' .mbp-pricing-type-input').filter(':checked').val();
        }

        this.toggleRoleView = function (event) {

            var $element = $(event.target);

            if ($element.hasClass('mbp-pricing-rule-action--delete')) {
                return;
            }
            var role = $element.closest('.mbp-pricing-rule');

            if (role.data('visible')) {
                this.hideRole(role);
            } else {
                this.showRole(role);
            }
        };

        this.showRole = function ($role) {
            $role.find('.mbp-pricing-rule__content').stop().slideDown(400);
            $role.find('.mbp-pricing-rule__action-toggle-view')
                .removeClass('mbp-pricing-rule__action-toggle-view--open')
                .addClass('mbp-pricing-rule__action-toggle-view--close');

            $role.data('visible', true);
        };

        this.hideRole = function ($role) {
            $role.find('.mbp-pricing-rule__content').stop().slideUp(400);
            $role.find('.mbp-pricing-rule__action-toggle-view')
                .removeClass('mbp-pricing-rule__action-toggle-view--close')
                .addClass('mbp-pricing-rule__action-toggle-view--open');
            $role.data('visible', false);
        };

        this.removeRole = function (e) {
            e.preventDefault();

            if (confirm("Are you sure?")) {

                var $roleToRemove = $(e.target).closest('.mbp-pricing-rule');
                var roleSlug = $roleToRemove.data('identifier-slug');

                this.$block.find('.mbp-add-new-rule-form__identifier-selector').append('<option value="' + roleSlug + '">' + $roleToRemove.data('identifier') + '</option>');

                $roleToRemove.slideUp(400, function () {
                    $roleToRemove.remove();
                });

                this.triggerVariationCanBeUpdated();
            }
        };

        this.block = function () {
            this.$block.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        };

        this.unblock = function () {
            this.$block.unblock();
        };

        this.addRole = function (event) {

            event.preventDefault();

            var selectedIdentifier = this.$block.find('.mbp-add-new-rule-form__identifier-selector').val();

            if (true) {

                var action = this.$block.data('add-action');
                var nonce = this.$block.data('add-action-nonce');
                var productId = this.$block.data('product-id');
                var loop = this.$block.data('loop');
                var type = this.$block.data('rule-type');

                $.ajax({
                    method: 'GET',
                    url: ajaxurl,
                    data: {
                        action: action,
                        nonce: nonce,
                        identifier: selectedIdentifier,
                        product_id: productId,
                        loop: loop,
                        type: type,
                    },
                    beforeSend: (function () {
                        this.block();
                    }).bind(this)
                }).done((function (response) {

                    if (response.success && response.role_row_html) {
                        this.$block.find('.mbp-pricing-rules').append(response.role_row_html);
                        this.$block.find('.mbp-no-rules').css('display', 'none');

                        $.each(this.$block.find('.mbp-pricing-rule'), (function (i, el) {
                            this.hideRole($(el));
                        }).bind(this));

                        this.showRole(this.$block.find('.mbp-pricing-rule').last());

                        this.$block.find('.mbp-add-new-rule-form__identifier-selector').find('[value="' + selectedIdentifier + '"]').remove();

                        $('.woocommerce-help-tip').tipTip({
                            'attribute': 'data-tip',
                            'fadeIn': 50,
                            'fadeOut': 50,
                            'delay': 200
                        });

                        this.triggerVariationCanBeUpdated();
                        $(document.body).trigger('wc-enhanced-select-init');

                    } else {
                        response.error_message && alert(response.error_message);
                    }
                    this.unblock();
                }).bind(this));
            }
        }

        this.triggerVariationCanBeUpdated = function () {

            if (!this.variationCanBeChangedAlreadyTriggered) {

                this.$block
                    .closest('.woocommerce_variation')
                    .addClass('variation-needs-update');

                jQuery('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
                jQuery('#variable_product_options').trigger('woocommerce_variations_defaults_changed');

                this.variationCanBeChangedAlreadyTriggered = true;
            }

        }
    };

    jQuery.each($('.mbp-pricing-rule-block'), function (i, el) {
        (new PricingRuleBlock()).init(jQuery(el).attr('id'));
    });

    jQuery(document).on('woocommerce_variations_loaded', function () {
        jQuery.each(jQuery('.mbp-pricing-rule-block'), function (i, el) {

            var $el = jQuery(el);

            if ($el.data('product-type') === 'variation') {
                (new PricingRuleBlock()).init($el.attr('id'));
            }
        });
    });

    jQuery(document).on('woocommerce_variations_saved', function () {
        jQuery.each(jQuery('.mbp-pricing-rule-block'), function (i, el) {
            (new PricingRuleBlock()).init(jQuery(el).attr('id'));
        });
    });

    jQuery('.mbp-select-woo').selectWoo();
});