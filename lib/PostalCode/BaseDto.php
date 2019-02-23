<?php

trait BaseDto {

    public function __call($name, $args) {
        $prefix = substr($name, 0, 3);
        $name = substr($name, 3);
        $name = lcfirst($name);
        switch ($prefix) {
            case "get":
                return $this->$name;
            case "set":
                $this->$name = $args[0];
                return;
        }
        trigger_error("undefined method " . $name, E_USER_ERROR);
    }

}
