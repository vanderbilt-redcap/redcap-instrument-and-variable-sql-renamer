<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

include_once(__DIR__ . "/classes/Autocomplete.php");

$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
$option = $_REQUEST['option'];
$data = Autocomplete::getAutocompleteData($module, $pid, $_REQUEST['term'], $type, $option);
echo $data;
?>
