<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\CurrencyInterface;

/**
 * Class CurrencyFactory
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyFactory
{
    /** @var string */
    protected $currencyClass;

    /**
     * @param string $currencyClass
     */
    public function __construct($currencyClass)
    {
        $this->currencyClass = $currencyClass;
    }

    /**
     * @return CurrencyInterface
     */
    public function create()
    {
        return new $this->currencyClass();
    }
}
