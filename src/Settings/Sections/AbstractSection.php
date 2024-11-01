<?php namespace MeowCrew\MembersBasedPricing\Settings\Sections;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;

abstract class AbstractSection {
	use ServiceContainerTrait;

	abstract public function getTitle();
	abstract public function getName();
	abstract public function getDescription();
	abstract public function getSettings();
}
