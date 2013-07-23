<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Pim\Bundle\ProductBundle\Entity\Family;

class FlatProductNormalizer implements NormalizerInterface
{
    private $results;

    public function normalize($object, $format = null, array $context = array())
    {
        $this->results = array();

        $this->normalizeValue($identifier = $object->getIdentifier());

        $this->normalizeFamily($object->getFamily());

        foreach ($object->getValues() as $value) {
            if ($value === $identifier) {
                continue;
            }
            $this->normalizeValue($value);
        }

        $this->normalizeCategories($object->getCategoryTitlesAsString());

        return $this->results;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'csv' === $format;
    }

    private function normalizeValue($value)
    {
        $suffix = '';
        if ($value->getAttribute()->getTranslatable()) {
            $suffix = sprintf('_%s', $value->getLocale());
        }
        $data = $value->getData();

        if ($data instanceof \DateTime) {
            $data = $data->format('r');
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            $data = '"' . join(',', $data->toArray()) . '"';
        }

        $this->results[$value->getAttribute()->getCode().$suffix] = (string) $data;
    }

    private function normalizeFamily(Family $family = null)
    {
        $this->results['family'] = $family ? $family->getCode() : '';
    }

    private function normalizeCategories($categories = '')
    {
        $this->results['categories'] = sprintf('"%s"', $categories);
    }
}
