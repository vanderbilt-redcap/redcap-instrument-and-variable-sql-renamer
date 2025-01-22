<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;
include_once(__DIR__ . "/classes/MessageHandler.php");

use Exception;
use REDCap;
use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use Twig\TwigFunction;

class REDCapInstrumentAndVariableSQLRenamer extends AbstractExternalModule
{

    public $messageHandler;

    public function redcap_module_link_check_display($project_id, $link)
    {
        if ($this->getUser()->isSuperUser()) {
            return parent::redcap_module_link_check_display($project_id, $link);
        }
        return false;
    }

    public function loadREDCapJS()
    {
        if (method_exists(get_parent_class($this), 'loadREDCapJS')) {
            parent::loadREDCapJS();
        } else {
            return "<script src='".APP_PATH_WEBROOT."Resources/webpack/js/bundle.js'></script>";
        }
    }

    public function loadTwigExtensions(): void
    {
        $function = new TwigFunction('redcap_get_instrument_names', [\REDCap::class, 'getInstrumentNames']);
        $this->getTwig()->addFunction($function);
    }

    public function getVariableList($pid): array
    {
        $sql = "SELECT form_name,field_name,element_label,field_order
                FROM redcap_metadata
                WHERE project_id = ?
                ORDER BY form_name";
        $result = $this->query($sql, [$pid]);
        $array = [];
        while ($row = $result->fetch_assoc()) {
         $array[] = $row;
        }
        return $array;
    }

    public function getInstrumentList($pid): array
    {
        $sql = "SELECT DISTINCT form_menu_description, form_name
					FROM redcap_metadata
					WHERE project_id = ? AND form_menu_description is not null
					ORDER BY form_menu_description";
        $result = $this->query($sql, [$pid]);
        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }
        return $array;
    }

    public function initialize()
    {
        $this->initializeTwig();
        $this->loadTwigExtensions();
    }

    public function getMessageHandler(): MessageHandler
    {
        if (!$this->messageHandler) {
            $this->messageHandler = new MessageHandler($this);
        }
        return $this->messageHandler;
    }
}

?>
