<?php

namespace Oro\Bundle\AddressBundle\Twig;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

class HasAddressExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'hasAddress' => new \Twig_Filter_Method($this, 'hasAddress')
        );
    }

    /**
     * Check whenever flexible entity contains not empty address attribute
     *
     * @param \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible $entity
     * @param null $addressCode
     * @return bool
     */
    public function hasAddress(AbstractEntityFlexible $entity, $addressCode = null)
    {
        if ($addressCode !== null) {
            $address = $entity->getValue($addressCode);

            return $address->getData() != null;
        }

        /** @var \Doctrine\Common\Collections\ArrayCollection $values **/
        $values = $entity->getValues();
        $values = $values->filter(
            function ($value) {
                if (strpos($value->getAttribute()->getCode(), 'address') !== false) {
                    return $value->getData() != null;
                }

                return false;
            }
        );
        return !$values->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_hasAddress';
    }
}
