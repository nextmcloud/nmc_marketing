<?php

namespace OCA\NmcMarketing\AppInfo;

use OCA\NmcMarketing\Listener\BeforeTemplateRenderedListener;
use OCA\NmcMarketing\Listener\CSPListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'nmc_marketing';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		//Register the CSPListener and consent layer redirect brake
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, CSPListener::class);
		$context->registerEventListener(BeforeLoginTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {

	}
}
