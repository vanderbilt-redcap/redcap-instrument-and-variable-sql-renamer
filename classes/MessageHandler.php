<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

use REDCap;

class MessageHandler
{
    private $module;

    public $message = '';

    public $messageType = '';

    public $variableList = '';

    public $instrumentList = '';

    public function __construct(REDCapInstrumentAndVariableSQLRenamer $module)
    {
        $this->module = $module;
    }

    public function setMessageType($messageType): void
    {
        $this->messageType = $messageType;
    }

    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function setVariableList($variableList): void
    {
        $this->variableList = $variableList;
    }

    public function setInstrumentList($instrumentList): void
    {
        $this->instrumentList = $instrumentList;
    }

    public function renderVariableList(): string
    {
        return $this->module->getTwig()->render("_variable_list.html.twig", ["variable_list" => $this->variableList]);
    }

    public function renderInstrumentList(): string
    {
        return $this->module->getTwig()->render("_instrument_list.html.twig", ["instrument_list" => $this->instrumentList]);
    }

    public function getMessageAttributes(): array
    {
        return [
            'message' => $this->message,
            "messageType" => $this->messageType,
            "variableListHtml" => $this->renderVariableList(),
            "instrumentListHtml" => $this->renderInstrumentList()
        ];
    }
}
