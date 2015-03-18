<?php

namespace Pim\Bundle\CatalogBundle\Denormalizer\Standard;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalizes a standard array format to an attribute option object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $objectClass;

    /** @var string[] */
    protected $supportedFormats = ['pim_array_standard'];

    /**
     * @param string $objectClass
     */
    public function __construct($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "code": "option_code",
     *     "attribute": "attribute_code",
     *     "labels": {
     *         "en_US": "My US Label",
     *         "fr_FR": "My FR Label"
     *     },
     *     "sort_order": 2
     * }
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!isset($context['object'])) {
            throw new InvalidArgumentException('Attribute option must be passed in the context');
        }
        /** @var AttributeOptionInterface $attributeOption */
        $attributeOption = $context['object'];

        // TODO: check input format, expected field with option resolver
        // TODO: option resolver for default value?

        foreach ($data['labels'] as $localeCode => $label) {
            $attributeOption->setLocale($localeCode);
            $translation = $attributeOption->getTranslation();
            $translation->setLabel($label);
        }
        $attributeOption->setSortOrder($data['sort_order']);

        return $attributeOption;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->objectClass && in_array($format, $this->supportedFormats);
    }

    /**
     * @return array
     */
    protected function getEditableProperties()
    {
        return [
            'label',
            'sort_order'
        ];
    }
}
