<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;

/**
 * Attribute options collection flat denormalizer used for following attribute types:
 * - pim_catalog_multiselect
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                AttributeTypes::OPTION_SIMPLE_SELECT,
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
