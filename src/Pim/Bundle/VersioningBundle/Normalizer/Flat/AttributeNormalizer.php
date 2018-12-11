<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AttributeInterface;
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
    const GLOBAL_SCOPE = 'Global';
    const CHANNEL_SCOPE = 'Channel';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var NormalizerInterface  */
    protected $translationNormalizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param $attribute AttributeInterface
     *
     * @return array
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $standardAttribute = $this->standardNormalizer->normalize($attribute, 'standard', $context);

        $flatAttribute = $standardAttribute;
        $flatAttribute['allowed_extensions'] = implode(self::ITEM_SEPARATOR, $standardAttribute['allowed_extensions']);
        $flatAttribute['available_locales'] = implode(self::ITEM_SEPARATOR, $standardAttribute['available_locales']);
        $flatAttribute['locale_specific'] = $attribute->isLocaleSpecific();

        unset($flatAttribute['labels']);
        $flatAttribute += $this->translationNormalizer->normalize($standardAttribute['labels'], 'flat', $context);

        $flatAttribute['options'] = $this->normalizeOptions($attribute);

        $flatAttribute['scope'] = $standardAttribute['scopable'] ? self::CHANNEL_SCOPE : self::GLOBAL_SCOPE;
        $flatAttribute['required'] = (bool) $attribute->isRequired();

        unset($flatAttribute['scopable']);

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
     *
     * @param AttributeInterface $object
     *
     * @return array
     */
    protected function normalizeOptions(AttributeInterface $attribute)
    {
        $options = $attribute->getOptions();

        if ($options->isEmpty()) {
            $options = null;
        } else {
            $data = [];
            foreach ($options as $option) {
                $data[] = 'Code:' . $option->getCode();
            }
            $options = implode(self::GROUP_SEPARATOR, $data);
        }

        return $options;
    }
}
