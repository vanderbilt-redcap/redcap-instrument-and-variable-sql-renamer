<?php
namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;
include_once(__DIR__ . "/classes/Autocomplete.php");

$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
$users = Autocomplete::getAutocompleteData($module, $pid, $_REQUEST['term'], $type);
echo $users;
?>