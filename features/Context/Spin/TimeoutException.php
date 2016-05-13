<?php

namespace Context\Spin;

class TimeoutException extends \Exception
{
    const TIMEOUT_BACKEND_PROCESS = 3;
    const TIMEOUT_UI = 1;
}
