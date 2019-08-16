<?php

require_once('./Customizing/global/plugins/Modules/Cloud/CloudHook/OneDrive/classes/Client/class.exodPath.php');

/**
 * Class ilOneDriveSettingsGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy ilOneDriveSettingsGUI : ilObjCloudGUI
 * @ingroup           ModulesCloud
 */
class ilOneDriveSettingsGUI extends ilCloudPluginSettingsGUI {

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	public function updateSettings() {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];

		try {
			$this->initSettingsForm();

			if ($this->form->checkInput()) {
				$_POST['title'] = exodPath::validateBasename($this->form->getInput("title"));
			}

			parent::updateSettings();
		} catch (Exception $e) {
			ilUtil::sendFailure($e->getMessage(), true);
			$ilCtrl->redirect($this, 'editSettings');
		}

	}


    public function initSettingsForm()
    {
        parent::initSettingsForm();

        $item = $this->form->getItemByPostVar("root_folder");
        $item->setTitle($this->txt("root_folder"));

    }


    public function txt($var = "")
    {
        return parent::txt('settings_' . $var);
    }


    /**
	 * @return ilOneDrive
	 */
	public function getPluginObject() {
		return parent::getPluginObject();
	}


    protected function getMakeOwnPluginSection()
    {
        return false;
    }
}

