<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Translation normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

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
            $translations[$translation->getLocale()] = $translation->$method();
        }

        return array($property => $translations);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractTranslation && in_array($format, $this->supportedFormats);
    }
}
