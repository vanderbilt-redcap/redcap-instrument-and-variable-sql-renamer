<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

use REDCap;

class MessageHandler
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

    public function setMessageType($messageType): void
    {
        $this->messageType = $messageType;
    }

    public function setMessage($message): void
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

    public function getMessageAttributes(): array
    {
        return [
            'message' => $this->message,
            "messageType" => $this->messageType,
            "printVariable" => $this->printVariable,
            "printInstrument" => $this->printInstrument
        ];
    }
}
