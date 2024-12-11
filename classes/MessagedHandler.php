<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

use REDCap;

class MessagedHandler
{
    private $module;

    public $message = '';

    public $messageType = '';

    public $printVariable = '';

    public $printInstrument = '';

    public function __construct(REDCapInstrumentAndVariableSQLRenamer $module)
    {
        $this->module = $module;
    }

    public function messageType($messageType): void
    {
        $this->messageType = $messageType;
    }

    public function addMessage($message): void
    {
        $this->message = $message;
    }

    public function setPrintVariable($printVariable): void
    {
        $this->printVariable = $printVariable;
    }

    public function setPrintInstrument($printInstrument): void
    {
        $this->printInstrument = $printInstrument;
    }
}