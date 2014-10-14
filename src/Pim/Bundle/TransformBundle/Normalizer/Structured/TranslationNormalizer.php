<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = array('json', 'xml');

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
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
            // TODO : throw an exception on master, not in 1.2 to avoid BC break
            if (method_exists($translation, $method) === false) {
                break;
            }
            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $translations[$translation->getLocale()] = $translation->$method();
            }
        }

        return array($context['property'] => $translations);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TranslatableInterface && in_array($format, $this->supportedFormats);
    }
}
