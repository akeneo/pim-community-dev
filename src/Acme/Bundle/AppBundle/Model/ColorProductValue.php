<?php

namespace Acme\Bundle\AppBundle\Model;

use Acme\Bundle\AppBundle\Entity\Color;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Color product value for "color" reference data. It's a new many to one relationship
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColorProductValue extends AbstractProductValue
{
    /** @var Color */
    protected $data;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param Color              $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, Color $data = null)
    {
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
        return $this->data->getCode();
    }
}
