<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;

class FlatProductNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        $results = array();

        foreach ($object->getValues() as $value) {
            $suffix = '';
            if ($value->getAttribute()->getTranslatable()) {
                $suffix = sprintf('_%s', $value->getLocale());
            }
            $data = $value->getData();

            if ($data instanceof \DateTime) {
                $data = $data->format('r');
            } else if ($data instanceof \Doctrine\Common\Collections\Collection) {
                $data = '"' . join(',', $data->toArray()) . '"';
            }

            $results[$value->getAttribute()->getCode().$suffix] = (string) $data;
        }

        return $results;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'csv' === $format;
    }
}
