<?php

namespace Docbox\utils;

class Result
{
    private $ok = false;
    private $messages = [];
    private $result = NULL;

    public function setOK($ok)
    {
        $this->ok = $ok;
    }

    public function isOK()
    {
        return $this->ok;
    }

    public function putMessage($message)
    {
        $this->messages[] = $message;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getFormattedMessage()
    {
        $message = "";

        for ($i = 0; $i < count($this->messages); $i++) {
            $message .= $this->messages[$i];
            if ($i + 1 < count($this->messages)) {
                $message .= "/n";
            }
        }

        return $message;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }
}
