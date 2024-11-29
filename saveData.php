<?php
namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
$new_var = $_REQUEST['new_var'];
$old_var = $_REQUEST['old_var'];
$var_name = ($type == "instrument") ? "form_name":"field_name";
$old_var_data = ($type == "instrument") ? $old_var."_complete":$old_var;

try{
    error_log(".......saveData");
    $module->query("START TRANSACTION",[]);
    error_log("START TRANSACTION");
    #Updating: Data/Form_complete Data
    $module->query("UPDATE redcap_data SET ".$var_name." = ? WHERE project_id = ? AND ".$var_name." = ?",[$new_var, $pid, $old_var_data]);
    for($i=2; $i < 7; $i++){
        error_log("redcap_data".$i);
        $qEvent = $module->query("UPDATE redcap_data".$i." SET ".$var_name." = ? WHERE project_id = ? AND ".$var_name." = ?",[$new_var, $pid, $old_var_data]);
        if(db_affected_rows() > 0){
            //write logs
        }
    }
    if($type == "variable") {
        #Updating: Metadata and Branching Logic
        $q = $module->query("SELECT branching_logic,field_name FROM redcap_metadata WHERE project_id = ? AND branching_logic like ?", [$pid, "%".$old_var."%"]);
        while ($row = $q->fetch_assoc()) {
            $new_branching_logic = str_replace($old_var,$new_var,$row['branching_logic']);
            $module->query("UPDATE redcap_metadata SET branching_logic = ? WHERE project_id = ? AND field_name = ?",[$new_branching_logic, $pid, $row['field_name']]);
        }
        $module->query("UPDATE redcap_metadata SET field_name = ? WHERE project_id = ? AND field_name = ?",[$new_var, $pid, $old_var]);
    }else if($type == "instrument"){
//        #Updating: Metadata, Surveys and Forms
//        $form_menu_description = $new_var;
//        $new_var = preg_replace("/[^a-z_0-9]/", "", str_replace(" ", "_", strtolower($new_var)));
//        $module->query("UPDATE redcap_metadata SET form_name = ?, form_menu_description = ? WHERE project_id = ? AND form_name = ?",[$new_var, $form_menu_description, $pid, $old_var]);
//        $module->query("UPDATE redcap_surveys SET form_name = ? WHERE project_id = ? AND form_name = ?",[$new_var, $pid, $old_var]);
//        $q = $module->query("SELECT b.event_id FROM redcap_events_arms a
//                JOIN redcap_events_metadata b ON b.arm_id = a.arm_id
//                WHERE a.project_id = ?"[$pid]);
//        while ($row = $q->fetch_assoc()) {
//            $module->query("UPDATE redcap_events_forms SET form_name = ? WHERE event_id = ? AND form_name = ?",[$new_var, $row['event_id'], $old_var]);
//        }
    }
    $module->query("COMMIT",[]);

    $_SESSION['message'] = ucfirst($type)." <strong>$old_var</strong> has been updated to <strong>$new_var</strong> successfully.";
    $_SESSION['message_type'] = "success";
    $status = "success_message";
}catch(Exception $e) {
    $module->query("ROLLBACK",[]);
    $_SESSION['message'] = "Something went wrong when updating the ".ucfirst($type)." <em>$old_var</em> to <strong>$new_var</strong>";
    $_SESSION['message_type'] = "warning_message";
    throw $e;
}

echo json_encode(array(
    'status' => "success"
));
?>