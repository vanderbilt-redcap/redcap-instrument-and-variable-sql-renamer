<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;
include_once(__DIR__ . "/classes/MessageHandler.php");

use Exception;
use REDCap;
use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

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
            ?>
            <script src='<?= APP_PATH_WEBROOT ?>Resources/webpack/js/bundle.js'></script>
            <?php
        }
    }

    public function printVariableList($pid): string
    {
        $sql = "SELECT form_name,field_name,element_label,field_order
					FROM redcap_metadata
					WHERE project_id = ?
					ORDER BY form_name";
        $result = $this->query($sql, [$pid]);
        $list_html = "";
        $aux = "";
        while ($row = $this->escape($result->fetch_assoc())) {
            if ($aux != $row['form_name']) {
                $aux = $row['form_name'];
                $list_html .= "<div class='group-header'>" . REDCap::getInstrumentNames($row['form_name']) . "</div>";
            }
            if ($row['field_name'] != $row['form_name'] . "_complete" && $row['field_order'] != "1") {
                $list_html .= "<div><a tabindex='0' role='button' class='info-toggle' data-html='true' data-container='body' data-toggle='tooltip' data-trigger='hover' data-placement='right' style='outline: none;' title='" . htmlspecialchars(
                        $row['element_label']
                    ) . "'><i class='fas fa-info-circle fa-fw' style='color:#0d6efd' aria-hidden='true'></i></a> <a onclick='addDataToInput(\"" . $row['field_name'] . "\")'>" . $row['field_name'] . "</a></div>";
            }
        }
        return $list_html;
    }

    public function printInstrumentList($pid): string
    {
        $sql = "SELECT DISTINCT form_menu_description, form_name
					FROM redcap_metadata
					WHERE project_id = ? AND form_menu_description is not null
					ORDER BY form_menu_description";
        $result = $this->query($sql, [$pid]);
        $list_html = "";
        while ($row = $this->escape($result->fetch_assoc())) {
            $list_html .= "<div><a onclick='addDataToInput(\"" . $row['form_name'] . "\")'>" . $row['form_menu_description'] . " <em>(" . $row['form_name'] . ")</em></a></div>";
        }
        return $list_html;
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
