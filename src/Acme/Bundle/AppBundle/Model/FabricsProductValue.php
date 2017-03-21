<?php

namespace Acme\Bundle\AppBundle\Model;

use Acme\Bundle\AppBundle\Entity\Fabric;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue as PimProductValue;

/**
 * Fabrics product value for "data" attribute type. It's a new many to many relationship
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FabricsProductValue extends AbstractProductValue
{
    /** @var Fabric[] */
    protected $data;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param Fabric[]           $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, array $data = [])
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
        $fabrics = [];
        foreach ($this->data as $fabric) {
            $fabrics[] = $fabric->getCode();
        }

        return implode(', ', $fabrics);
    }
}
