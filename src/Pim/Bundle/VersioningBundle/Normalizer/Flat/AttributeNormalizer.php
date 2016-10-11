<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Standard\AttributeNormalizer as StandardNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    const ITEM_SEPARATOR = ',';
    const LOCALIZABLE_PATTERN = '{locale}:{value}';
    const GROUP_SEPARATOR = '|';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /** @var TranslationNormalizer  */
    protected $translationNormalizer;

    /**
     * AttributeNormalizer constructor.
     *
     * @param NormalizerInterface   $standardNormalizer
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param $object AttributeInterface
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $standardAttribute = $this->standardNormalizer->normalize($object, 'standard', $context);

        $flatAttribute = $standardAttribute;
        $flatAttribute['allowed_extensions'] = implode(self::ITEM_SEPARATOR, $standardAttribute['allowed_extensions']);
        $flatAttribute['available_locales'] = implode(self::ITEM_SEPARATOR, $standardAttribute['available_locales']);

        unset($flatAttribute['labels']);
        $flatAttribute += $this->translationNormalizer->normalize($standardAttribute['labels'], 'flat', $context);

        $flatAttribute['options'] = $this->normalizeOptions($object);

        return $flatAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeOptions(AttributeInterface $attribute)
    {
        $options = $attribute->getOptions();

        if ($options->isEmpty()) {
            $options = null;
        } else {
            $data = [];
            foreach ($options as $option) {
                $item = [];
                foreach ($option->getOptionValues() as $value) {
                    /* @var ProductValueInterface $value */
                    $label = str_replace('{locale}', $value->getLocale(), self::LOCALIZABLE_PATTERN);
                    $label = str_replace('{value}', $value->getValue(), $label);
                    $item[] = $label;
                }
                $data[] = 'Code:'.$option->getCode().self::ITEM_SEPARATOR.implode(self::ITEM_SEPARATOR, $item);
            }
            $options = implode(self::GROUP_SEPARATOR, $data);
        }

        return $options;
    }
}
