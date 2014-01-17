<?php

namespace Pim\Bundle\FlexibleEntityBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * Filter attribute extension
 */
class FilterAttributesExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'getAttributes' => new \Twig_Filter_Method($this, 'getAttributes')
        );
    }

    /**
     * Returns attribute values array and filter if attributes array filled
     * |getAttributes() - will return all attributes
     * |getAttributes('address') - will return attribute with code "address"
     * |getAttributes(['firstName', 'lastName']) - will filter by few attribute codes
     *
     * If third parameter equals true method will return attributes that not in given array(or string)
     *
     * @param AbstractEntityFlexible $entity
     * @param string|array           $attributes
     * @param boolean                $skip
     *
     * @return ArrayCollection
     */
    public function getAttributes(AbstractEntityFlexible $entity, $attributes = array(), $skip = false)
    {
        if (!empty($attributes) && !is_array($attributes)) {
            $attributes = array($attributes);
        }

        /** @var ArrayCollection $values */
        $values = $entity->getValues();

        if ($values->isEmpty() || empty($attributes)) {
            $values = $skip ? new ArrayCollection() : $values;

            return $values;
        }

        $values = $values->filter(
            function ($value) use ($attributes, $skip) {
                if (in_array($value->getAttribute()->getCode(), $attributes)) {
                    return !$skip;
                }

                return $skip;
            }
        );

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_getAttributes';
    }
}
