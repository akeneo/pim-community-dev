<?php

class toto {
    private $families;

    public static function fromApiFormat(): toto
    {
        $family = null;
        $test = new self();
        $test->families = "toto";

        return $test;
    }

    public function families(): ?string
    {
        return $this->families;
    }
}

var_dump(toto::fromApiFormat()->families());
