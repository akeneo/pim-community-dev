<?php

namespace Pim\Component\Catalog\Model\ProductValue;

use Pim\Component\Catalog\Model\AbstractProductValue;

/**
 * Product value for:
 *   - pim_catalog_identifier
 *   - pim_catalog_text
 *   - pim_catalog_textarea
 *   - pim_catalog_boolean
 *   - pim_catalog_number
 *   - pim_catalog_option
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValue extends AbstractProductValue
{
    /** @var string */
    protected $data;

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->data;
    }
}
