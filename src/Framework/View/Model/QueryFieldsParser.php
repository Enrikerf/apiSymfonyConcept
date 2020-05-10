<?php

namespace App\Framework\View\Model;

class QueryFieldsParser {

    protected $stack        = null;
    protected $length       = null;
    protected $current      = null;
    protected $string       = null;
    protected $position     = null;
    protected $buffer_start = null;

    public function __construct() {
        return $this;
    }

    public function parse($string) {
        if (!$string) {
            return [];
        }
        ($string[0] == '(') ? $string = substr($string, 1, -1) : null;
        $this->current = [];
        $this->stack = [];
        $this->string = $string;
        $this->length = strlen($this->string);
        for ($this->position = 0; $this->position < $this->length; $this->position++) {
            switch ($this->string[$this->position]) {
                case '(':
                    $this->push();
                    array_push($this->stack, $this->current);
                    $this->current = [];
                    break;
                case ')':
                    $this->push();
                    $t = $this->current;
                    $this->current = array_pop($this->stack);
                    $this->current[] = $t;
                    break;
                case ',':
                    $this->push();
                    break;
                default:
                    if ($this->buffer_start === null) {
                        $this->buffer_start = $this->position;
                    }
            }
        }
        $this->push();

        return $this->recursive($this->current);
    }

    private function recursive($array) {
        $lastKey = '';
        $lastArray = [];
        for ($i = 0; $i < count($array); $i++) {
            if (is_array($array[$i])) {
                if ($lastKey) {
                    $lastArray[$lastKey] = $this->recursive($array[$i]);
                    $lastKey = '';
                }
            } else {
                if ($lastKey) {
                    $lastArray[] = $lastKey;
                    $lastKey = $array[$i];
                } else {
                    $lastKey = $array[$i];
                }
            }
        }
        ($lastKey) ? $lastArray[] = $lastKey : null;

        return $lastArray;
    }

    protected function push() {
        if ($this->buffer_start !== null) {
            $buffer = substr($this->string, $this->buffer_start, $this->position - $this->buffer_start);
            $this->buffer_start = null;
            $this->current[] = $buffer;
        }
    }
}