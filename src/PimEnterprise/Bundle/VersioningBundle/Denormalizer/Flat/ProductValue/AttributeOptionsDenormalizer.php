<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute options collection flat denormalizer used for following attribute types:
 * - pim_catalog_multiselect
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsDenormalizer extends AbstractValueDenormalizer
{
    /** @var  AttributeOptionDenormalizer */
    protected $denormalizer;

    /**
     * @param array                       $supportedTypes
     * @param AttributeOptionDenormalizer $denormalizer
     */
    public function __construct(array $supportedTypes, AttributeOptionDenormalizer $denormalizer)
    {
        parent::__construct($supportedTypes);
        $this->denormalizer = $denormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($data === null || $data === '') {
            return null;
        }

        $options = new ArrayCollection();
        foreach (explode(',', $data) as $optionCode) {
            $option = $this->denormalizer->denormalize(
                $optionCode,
                'pim_catalog_simpleselect',
                $format,
                $context
            );
            if (null !== $option) {
                $options->add($option);
            }
        }

        return $options;
    }
}
