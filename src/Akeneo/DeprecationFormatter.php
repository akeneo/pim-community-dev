<?php

namespace Akeneo;

use Monolog\Formatter\LineFormatter;

/**
 * Remove datetime from logs in order to be able to sort and make deprecation uniques easily.
 */
class DeprecationFormatter extends LineFormatter
{
    const FORMAT_WITHOUT_DATETIME = "%channel%.%level_name%: %message% %context% %extra%\n";

    public function __construct()
    {
        parent::__construct(self::FORMAT_WITHOUT_DATETIME);
    }
}
