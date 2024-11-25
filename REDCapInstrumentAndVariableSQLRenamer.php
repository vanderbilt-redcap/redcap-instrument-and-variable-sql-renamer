<?php
namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

use Exception;
use REDCap;
use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

class REDCapInstrumentAndVariableSQLRenamer extends AbstractExternalModule
{

    public function loadREDCapJS(){
        if (method_exists(get_parent_class($this), 'loadREDCapJS')) {
            parent::loadREDCapJS();
        } else {
            ?>
            <script src='<?=APP_PATH_WEBROOT?>Resources/webpack/js/bundle.js'></script>
            <?php
        }
    }

    public function printVariableList($pid){
        $sql = "SELECT form_name,field_name,element_label
					FROM redcap_metadata
					WHERE project_id = ?
					 AND element_type IN ('select','radio','checkbox','yesno','truefalse')
					ORDER BY form_name";
        $result = $this->query($sql, [$pid]);
        $list_html = "";
        $aux = "";
        while ($row = $result->fetch_assoc()) {
            if ($aux != $row['form_name']){
                $aux = $row['form_name'];
                $list_html .= "<div class='group-header'>" . \REDCap::getInstrumentNames($row['form_name']) . "</div>";
            }
            $list_html.= "<div><a tabindex='0' role='button' class='info-toggle' data-html='true' data-container='body' data-toggle='tooltip' data-trigger='hover' data-placement='right' style='outline: none;' title='".htmlspecialchars($row['element_label'])."'><i class='fas fa-info-circle fa-fw' style='color:#0d6efd' aria-hidden='true'></i></a> <a onclick='addDataToInput(\"" .$row['field_name']. "\")'>".$row['field_name']."</a></div>";
        }
        return $list_html;
    }

    public function printInstrumentList($pid){
        $sql = "SELECT DISTINCT form_name
					FROM redcap_metadata
					WHERE project_id = ?
					ORDER BY field_order";
        $result = $this->query($sql, [$pid]);
        $list_html = "";
        while ($row = $result->fetch_assoc()) {
            $list_html.= "<div><a onclick='addDataToInput(\"" .$row['form_name']. "\")'>".$row['form_name']."</a></div>";
        }
        return $list_html;
    }
}
?>