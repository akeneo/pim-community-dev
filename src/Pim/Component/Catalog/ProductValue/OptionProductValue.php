<?php

namespace Pim\Component\Catalog\ProductValue;

use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Product value for "pim_catalog_simpleselect" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionProductValue extends AbstractProductValue implements OptionProductValueInterface
{
    /** @var AttributeOptionInterface[] */
    protected $data;

    /**
     * @param AttributeInterface            $attribute
     * @param string                        $channel
     * @param string                        $locale
     * @param AttributeOptionInterface|null $data
     */
    public function __construct(
        AttributeInterface $attribute,
        $channel,
        $locale,
        AttributeOptionInterface $data = null
    ) {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
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
        return null !== $this->data ? $this->data->getCode() : '';
    }
}
