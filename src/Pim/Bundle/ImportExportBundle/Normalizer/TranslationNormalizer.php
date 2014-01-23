<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;

/**
 * Translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context = array_merge(
            [
                'property' => 'label',
                'locales'  => [],
            ],
            $context
        );

        $translations = array_fill_keys($context['locales'], '');
        $method = sprintf('get%s', ucfirst($context['property']));

        foreach ($object->getTranslations() as $translation) {
            if (method_exists($translation, $method)) {
                $translations[$translation->getLocale()] = $translation->$method();
            }
        }

        return [$context['property'] => $translations];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TranslatableInterface && in_array($format, $this->supportedFormats);
    }
}
