<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionsDenormalizer extends AttributeOptionDenormalizer
{
    /** @var array */
    protected $supportedTypes = array('pim_catalog_multiselect');

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

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && 'csv' === $format;
    }
}
