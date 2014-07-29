<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute options collection flat denormalizer used for following attribute types:
 * - pim_catalog_multiselect
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionsDenormalizer extends AttributeOptionDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $options = new ArrayCollection();
        foreach (explode(',', $data) as $optionCode) {
            $options->add(
                parent::denormalize($optionCode, 'pim_catalog_simpleselect', $format, $context)
            );
        }

        return $options;
    }
}
