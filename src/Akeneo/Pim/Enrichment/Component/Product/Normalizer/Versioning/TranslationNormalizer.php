<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface
{
    const LABEL_SEPARATOR = '-';

    /**  @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param array $translatable
     *
     * @return array
     */
    public function normalize($translatable, $format = null, array $context = [])
    {
        $context = array_merge(
            [
                'field_name' => 'label',
                'locales'    => [],
            ],
            $context
        );
        $property = $context['field_name'];
        $localCodes = $context['locales'];

        $translations = [];
        foreach ($translatable as $localeCode => $translation) {
            if (empty($localCodes) || in_array($localeCode, $localCodes)) {
                $translations[$property . self::LABEL_SEPARATOR . $localeCode] = $translation;
            }
        }

        foreach ($localCodes as $localeCode) {
            if (!isset($translations[$property . self::LABEL_SEPARATOR . $localeCode])) {
                $translations[$property . self::LABEL_SEPARATOR . $localeCode] = '';
            }
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_array($data) && in_array($format, $this->supportedFormats) && isset($data['labels']);
    }
}
