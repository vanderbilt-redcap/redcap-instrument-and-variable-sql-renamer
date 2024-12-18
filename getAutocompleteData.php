<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

include_once(__DIR__ . "/classes/Autocomplete.php");

$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
$option = $_REQUEST['option'];

$autocomplete = new Autocomplete();
$data = $autocomplete->getAutocompleteData($module, $pid, $_REQUEST['term'], $type, $option);

echo $data;
?>
