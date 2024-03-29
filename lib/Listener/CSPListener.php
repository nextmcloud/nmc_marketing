<?php

declare(strict_types=1);

namespace OCA\NmcMarketing\Listener;

use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class CSPListener implements IEventListener {

	private IRequest $request;
	private IConfig $iConfig;

	public function __construct(IRequest $request, IConfig $iConfig) {
		$this->request = $request;
		$this->iConfig = $iConfig;
	}

	public function handle(Event $event): void {
		if (!$event instanceof AddContentSecurityPolicyEvent) {
			return;
		}
		if (!$this->isPageLoad()) {
			return;
		}

		//This loading from the config the trusted urls
		$marketing_config = $this->iConfig->getSystemValue("nmc_marketing");

		$policy = new EmptyContentSecurityPolicy();
		$policy->useStrictDynamic(true);

		//This add the trusted script urls to the CSP
		foreach ($marketing_config['trusted_script_urls'] as $trusted_url) {
			$policy->addAllowedScriptDomain($this->domainOnly($trusted_url));
		}

		//This is the exception for specific user agents and pages
		if ($this->request->getPathInfo() === '/' ||
			 $this->request->getPathInfo() === '/login' ||
			 $this->request->getPathInfo() === '/login/flow/grant') {
			$policy->addAllowedScriptDomain("'unsafe-inline'");
		}

		//This add the trusted font urls to the CSP
		foreach ($marketing_config['trusted_font_urls'] as $trusted_url) {
			$policy->addAllowedFontDomain($this->domainOnly($trusted_url));
		}

		//This add the trusted image urls to the CSP
		foreach ($marketing_config['trusted_image_urls'] as $image_url) {
			$policy->addAllowedImageDomain($this->domainOnly($image_url));
		}

		//Add the policy to the event
		$event->addPolicy($policy);
	}

	private function isPageLoad(): bool {
		$scriptNameParts = explode('/', $this->request->getScriptName());
		return end($scriptNameParts) === 'index.php';
	}

	/**
	 * Strips the path and query parameters from the URL.
	 */
	private function domainOnly(string $url): string {
		$parsedUrl = parse_url($url);
		$scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : 'https://';
		$host = $parsedUrl['host'] ?? '';
		$port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
		return "$scheme$host$port";
	}
}
