<?php namespace MeowCrew\MembersBasedPricing\Core;

trait ServiceContainerTrait {

	public function getContainer() {
		return ServiceContainer::getInstance();
	}

}
