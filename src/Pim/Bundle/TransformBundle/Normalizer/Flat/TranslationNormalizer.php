<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * Flat translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer extends Structured\TranslationNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
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

        $property = $context['property'];
        $translations = array_fill_keys(
            array_map(
                function ($locale) use ($property) {
                    return sprintf('%s-%s', $property, $locale);
                },
                $context['locales']
            ),
            ''
        );

        $method = sprintf('get%s', ucfirst($property));
        foreach ($object->getTranslations() as $translation) {
            if (method_exists($translation, $method) === false) {
                throw new \LogicException(
                    sprintf("Class %s doesn't provide method %s", get_class($translation), $method)
                );
            }
            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $key = sprintf('%s-%s', $property, $translation->getLocale());
                $translations[$key] = $translation->$method();
            }
        }

        return $translations;
    }
}
