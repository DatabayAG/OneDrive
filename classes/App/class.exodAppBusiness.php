<?php
require_once('class.exodApp.php');
require_once('./Customizing/global/plugins/Modules/Cloud/CloudHook/OneDrive/classes/App/class.exodTenant.php');

/**
 * Class exodAppBusiness
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class exodAppBusiness extends exodApp {

	/**
	 * @var int
	 */
	protected $type = self::TYPE_BUSINESS;
	/**
	 * @var string
	 */
	protected $tenant_id = '';
	/**
	 * @var string
	 */
	protected $tenant_name = '';


	public function buildURLs() {
		$this->setBaseUrl('https://login.windows.net/' . $this->getTenantId() . '/oauth2/');
		$this->setAuthUrl($this->getBaseUrl() . 'authorize');
		$this->setTokenUrl($this->getBaseUrl() . 'token');
		$this->setRessourceUri('https://' . $this->getTenantName() . '-my.sharepoint.com/');
		$this->setRessource('https://' . $this->getTenantName()
		                    . '-my.sharepoint.com/_api/v1.0/me');
	}


	/**
	 * @param exodBearerToken $exod_bearer_token
	 * @param                 $client_id
	 * @param                 $client_secret
	 *
	 * @return exodAppBusiness
	 */
	public static function getInstance(exodBearerToken $exod_bearer_token, $client_id, $client_secret, exodTenant $exodTenant) {
		//		if (!isset(self::$instance)) {
		self::$instance = new self($exod_bearer_token, $client_id, $client_secret, $exodTenant);

		//		}

		return self::$instance;
	}


	protected function initRedirectUri() {
		$this->setRedirectUri($this->getHttpPath()
		                      . 'Customizing/global/plugins/Modules/Cloud/CloudHook/OneDrive/redirect.php');
	}


	/**
	 * @param exodBearerToken $exod_bearer_token
	 * @param                 $client_id
	 * @param                 $client_secret
	 * @param exodTenant $exodTenant
	 */
	protected function __construct(exodBearerToken $exod_bearer_token, $client_id, $client_secret, exodTenant $exodTenant) {
		$this->tenant_id = $exodTenant->getTenantId();
		$this->tenant_name = $exodTenant->getTenantName();
		parent::__construct($exod_bearer_token, $client_id, $client_secret); // TODO: Change the autogenerated stub
	}


	/**
	 * @return string
	 */
	public function getTenantId() {
		return $this->tenant_id;
	}


	/**
	 * @param string $tenant_id
	 */
	public function setTenantId($tenant_id) {
		$this->tenant_id = $tenant_id;
	}


	/**
	 * @return string
	 */
	public function getTenantName() {
		return $this->tenant_name;
	}


	/**
	 * @param string $tenant_name
	 */
	public function setTenantName($tenant_name) {
		$this->tenant_name = $tenant_name;
	}
}

?>
