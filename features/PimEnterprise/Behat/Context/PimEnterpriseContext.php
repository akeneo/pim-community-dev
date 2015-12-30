<?php

namespace PimEnterprise\Behat\Context;

use Pim\Behat\Context\PimContext;

class PimEnterpriseContext extends PimContext
{
    /** @var array */
    protected static $placeholderValues = [];

    public function __construct()
    {
        self::resetPlaceholderValues();
    }

    /**
     * Reset placeholder values
     */
    public static function resetPlaceholderValues()
    {
        self::$placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            //TODO: change that later
            '%fixtures%' => __DIR__ . '/../../../Context/fixtures'
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function replacePlaceholders($value)
    {
        return strtr($value, self::$placeholderValues);
    }
}
