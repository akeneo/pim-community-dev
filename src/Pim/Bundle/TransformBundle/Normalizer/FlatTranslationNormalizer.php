<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

/**
 * Flat translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatTranslationNormalizer extends TranslationNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

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
            if (method_exists($translation, $method)) {
                $key = sprintf('%s-%s', $property, $translation->getLocale());
                $translations[$key] = $translation->$method();
            }
        }

        return $translations;
    }
}
