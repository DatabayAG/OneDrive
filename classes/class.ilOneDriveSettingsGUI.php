<?php

use srag\Plugins\OneDrive\EventLog\UI\EventLogTableUI;

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

    const SUBTAB_GENERAL = 'general';
    const SUBTAB_LOGS = 'logs';
    const SETTINGS = self::class;

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;

    public function __construct($plugin_service_class)
    {
        parent::__construct($plugin_service_class);
    }


    protected function initPluginSettings()
    {
        global $DIC;
        $this->settings = self::createSettings($this->cloud_object->getId());
        $this->form->getItemByPostVar('root_folder')->setDisabled(true);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->getPluginHookObject()->txt('schedule'));
        $this->form->addItem($section);

        $start_date = new ilDateTimeInputGUI($this->getPluginHookObject()->txt('start_date'), 'start_date');
        $this->form->addItem($start_date);

        $end_date = new ilDateTimeInputGUI($this->getPluginHookObject()->txt('end_date'), 'end_date');
        $start_date->addSubItem($end_date);
        $extra_date = new ilDateTimeInputGUI($this->getPluginHookObject()->txt('extra_date'), 'extra_date');
        $end_date->addSubItem($extra_date);
    }

    protected function getPluginSettingsValues(&$values)
    {
        $values['start_date'] = $this->dateOf('start_date');
        $values['end_date'] = $this->dateOf('end_date');
        $values['extra_date'] = $this->dateOf('extra_date');
    }

    private function dateOf(string $key)
    {
        $value = ilCalendarUtil::parseIncomingDate($this->settings->get($key, ''), 0);
        if (is_object($value)) {
            return $value->get(IL_CAL_DATE);
        }
        return $value;
    }

    public function updateSettings() {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$this->initSubtabs(self::SUBTAB_GENERAL);

		try {
			$this->initSettingsForm();

			if ($this->form->checkInput()) {
				$_POST['title'] = exodPath::validateBasename($this->form->getInput("title"));
				array_map([$this, 'save'], ['start_date', 'end', 'end_date', 'extra_date']);
			}

			parent::updateSettings();
		} catch (Exception $e) {
			ilUtil::sendFailure($e->getMessage(), true);
			$ilCtrl->redirect($this, 'editSettings');
		}

	}


    function editSettings()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->initSubtabs(self::SUBTAB_GENERAL);

        $cloud_object_changed = false;

        // On object creation set cloud root id
        if (isset($_GET["root_id"])) {
            $this->applyRootId();
            $cloud_object_changed = true;
            ilUtil::sendSuccess($lng->txt("cld_object_added"), true);
        }

        $service = ilCloudConnector::getServiceClass($this->cloud_object->getServiceName(), $this->cloud_object->getId());

        if ($service->updateRootFolderPosition($this->cloud_object->getRootId())) {
            $cloud_object_changed = true;
        }

        if ($cloud_object_changed) {

            $ilCtrl->redirectByClass("ilCloudPluginSettingsGUI", "editSettings");
        }

        parent::editSettings();
    }


    protected function applyRootId() {
        $this->cloud_object->setRootId($_GET["root_id"]);
        $this->cloud_object->update();
    }


    public function initSettingsForm()
    {
        parent::initSettingsForm();

        $item = $this->form->getItemByPostVar("root_folder");
        $item->setTitle($this->txt("root_folder"));

    }

    protected function showLogs()
    {
        global $DIC;
        $DIC->tabs()->activateTab('settings');
        $this->initSubtabs(self::SUBTAB_LOGS);
        $DIC->ui()->mainTemplate()->setContent(
            $this->getPluginObject()->databay()->exportEventLog($this->getPluginObject()->getObjId())->render() .
            (new EventLogTableUI($DIC, $this->getPluginObject()->getObjId()))->render()
        );
    }

    protected function initSubtabs(string $active)
    {
        global $DIC;
        $DIC->tabs()->addSubTab(
            self::SUBTAB_GENERAL,
            $this->txt('subtab_' . self::SUBTAB_GENERAL),
            $DIC->ctrl()->getLinkTargetByClass(parent::class, 'editSettings'));
        $DIC->tabs()->addSubTab(
            self::SUBTAB_LOGS,
            $this->txt('subtab_' . self::SUBTAB_LOGS),
            $DIC->ctrl()->getLinkTargetByClass(parent::class, 'showLogs'));
        $DIC->tabs()->setSubTabActive($active);
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

    private function save(string $key)
    {
        $this->settings->set($key, (string) $this->form->getInput($key));
    }

    public static function createSettings(int $id): ilSetting
    {
        return new ilSetting(self::SETTINGS . '_' . $id);
    }
}

