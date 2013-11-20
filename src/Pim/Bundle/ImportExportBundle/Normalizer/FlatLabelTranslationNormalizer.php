<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

/**
 * Flat label translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatLabelTranslationNormalizer extends LabelTranslationNormalizer
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
        $translations = array();
        foreach ($object->getTranslations() as $translation) {
            $translations[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $translations;
    }
}
