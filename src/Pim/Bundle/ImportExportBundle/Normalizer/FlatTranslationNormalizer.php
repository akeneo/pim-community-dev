<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

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
        if (!isset($context['property'])) {
            $property = 'label';
        }
        $method = 'get'. ucfirst($property);

        $translations = array();
        foreach ($object->getTranslations() as $translation) {
            $key = sprintf('%s-%s', $property, $translation->getLocale());
            $translations[$key] = $translation->$method();
        }

        return $translations;
    }
}
