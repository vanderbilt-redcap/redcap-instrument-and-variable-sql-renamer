<?php
$pid = (int)$_GET['pid'];
$module->initialize();
echo $module->getTwig()->render('index.html.twig', [
    'name' => 'World',
    'redcap_js' => $module->loadREDCapJS(),
    'pid' => $pid,
    'variable_list' => $module->getVariableList($pid),
    'instrument_list' => $module->getInstrumentList($pid)
    ]
);
