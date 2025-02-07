<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;
include_once(__DIR__ . "/classes/MessageHandler.php");

use REDCap;

$module->initialize();

$pid = (int)$_REQUEST['pid'];
$type = htmlentities($_REQUEST['type'], ENT_QUOTES) ?? null;
$new_var = htmlentities($_REQUEST['new_var'], ENT_QUOTES) ?? null;
$old_var = htmlentities($_REQUEST['old_var'], ENT_QUOTES) ?? null;
$old_var_data = ($type == "instrument") ? $old_var . "_complete" : $old_var;
if ($type == "instrument") {
    $form_menu_description_new = $new_var;
    $new_var = preg_replace("/[^a-z_0-9]/", "", str_replace(" ", "_", strtolower($new_var)));
    $form_menu_description_old = \REDCap::getInstrumentNames(trim(strtolower($old_var)));
}
$new_var_data = ($type == "instrument") ? $new_var . "_complete" : $new_var;
$logging_message = "Changes made by user: " . USERID . "\nAffected tables: \n";
$logging_title = "Changed $type <strong>" . $old_var . "</strong> to <strong>" . $new_var . "</strong>\n";
try {
    $module->query("START TRANSACTION", []);
    #Updating: Data/Form_complete Data
    $module->query(
        "UPDATE " . $module->getDataTable($pid) . " SET field_name = ? WHERE project_id = ? AND field_name = ?",
        [$new_var_data, $pid, $old_var_data]
    );
    $logging_message .= "• " . $module->getDataTable($pid) . ": field_name\n";
    if ($type == "variable") {
        #Updating: Metadata and Branching Logic
        $q = $module->query(
            "SELECT branching_logic,field_name FROM redcap_metadata WHERE project_id = ? AND branching_logic like ?",
            [$pid, "%" . $old_var . "%"]
        );
        while ($row = $q->fetch_assoc()) {
            $new_branching_logic = str_replace($old_var, $new_var, $row['branching_logic']);
            $module->query(
                "UPDATE redcap_metadata SET branching_logic = ? WHERE project_id = ? AND field_name = ?",
                [$new_branching_logic, $pid, $row['field_name']]
            );
            $logging_message .= "• redcap_metadata: branching_logic\n";
        }
        $module->query(
            "UPDATE redcap_metadata SET field_name = ? WHERE project_id = ? AND field_name = ?",
            [$new_var, $pid, $old_var]
        );
        $logging_message .= "• redcap_metadata: field_name\n";
    } else {
        if ($type == "instrument") {
            #Updating: Metadata, Surveys and Forms
            $module->query(
                "UPDATE redcap_metadata SET form_name = ? WHERE project_id = ? AND form_name = ?",
                [$new_var, $pid, $old_var]
            );
            $module->query(
                "UPDATE redcap_metadata SET field_name = ? WHERE project_id = ? AND field_name = ?",
                [$new_var_data, $pid, $old_var_data]
            );
            $module->query(
                "UPDATE redcap_metadata SET form_menu_description = ? WHERE project_id = ? AND form_menu_description = ?",
                [$form_menu_description_new, $pid, $form_menu_description_old]
            );
            $module->query(
                "UPDATE redcap_surveys SET form_name = ?, title = ? WHERE project_id = ? AND form_name = ? AND title = ?",
                [$new_var, $form_menu_description_new, $pid, $old_var, $form_menu_description_old]
            );
            $logging_message .= "• redcap_metadata: form_name\n";
            $logging_message .= "• redcap_metadata: field_name\n";
            $logging_message .= "• redcap_metadata: form_menu_description\n";
            $logging_message .= "• redcap_surveys: form_name\n";
            $q = $module->query(
                "SELECT b.event_id FROM redcap_events_arms a JOIN redcap_events_metadata b ON b.arm_id = a.arm_id WHERE a.project_id = ?",
                [$pid]
            );
            while ($row = $q->fetch_assoc()) {
                $qForm = $module->query(
                    "SELECT event_id FROM redcap_events_forms WHERE form_name = ? AND event_id = ?",
                    [$new_var,$row['event_id']]
                );
                if ($qForm->num_rows == 0) {
                    $module->query(
                        "UPDATE redcap_events_forms SET form_name = ? WHERE event_id = ? AND form_name = ?",
                        [$new_var, $row['event_id'], $old_var]
                    );
                    $logging_message .= "• redcap_events_forms: form_name\n";
                }
                $q2 = $module->query(
                    "SELECT event_id, form_name, custom_repeat_form_label FROM redcap_events_repeat WHERE event_id = ? AND form_name = ?;",
                    [$row['event_id'], $old_var]
                );
                while ($row2 = $q2->fetch_assoc()) {
                    $module->query(
                        "UPDATE redcap_events_repeat SET form_name = ? WHERE event_id = ? AND form_name = ?",
                        [$new_var, $row2['event_id'], $old_var]
                    );
                    $logging_message .= "• redcap_events_repeat: form_name\n";
                }
            }
        }
    }
    $module->query("COMMIT", []);
    REDCap::logEvent($logging_title, $logging_message, null, null, null, $pid);
    $message = ucfirst(
            $type
        ) . " <strong>$old_var</strong> has been updated to <strong>$new_var</strong> successfully.";
    $module->getMessageHandler()->setMessage($message);
    $module->getMessageHandler()->setMessageType('success');
    $module->getMessageHandler()->setVariableList($module->getVariableList($pid));
    $module->getMessageHandler()->setInstrumentList($module->getInstrumentList($pid));
} catch (Exception $e) {
    $module->query("ROLLBACK", []);
    $message = "Something went wrong when updating the " . ucfirst(
            $type
        ) . " <em>$old_var</em> to <strong>$new_var</strong>";
    $module->getMessageHandler()->setMessage($message);
    $module->getMessageHandler()->setMessageType('danger');
    throw $e;
}

echo json_encode($module->getMessageHandler()->getMessageAttributes());
?>
