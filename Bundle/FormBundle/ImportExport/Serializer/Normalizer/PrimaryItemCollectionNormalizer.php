<?php

namespace Oro\Bundle\FormBundle\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\CollectionNormalizer;

use Oro\Bundle\FormBundle\Entity\PrimaryItem;

class PrimaryItemCollectionNormalizer extends CollectionNormalizer
{
    const PRIMARY_ITEM_TYPE = 'Oro\Bundle\FormBundle\Entity\PrimaryItem';

    /**
     * Returned normalized data where first element is primary
     *
     * @param Collection $object object to normalize
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $result = array();

        /** @var $item PrimaryItem */
        foreach ($object as $item) {
            $serializedItem = $this->serializer->normalize($item, $format, $context);
            if ($item->isPrimary()) {
                array_unshift($result, $serializedItem);
            } else {
                $result[] = $serializedItem;
            }
        }

        return $result;
    }

    /**
     * Denormalizes and sets primary to first element
     *
     * @param mixed $data
     * @param string $class
     * @param null $format
     * @param array $context
     * @return ArrayCollection
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = parent::denormalize($data, $class, $format, $context);
        $primary = true;
        /** @var $item PrimaryItem */
        foreach ($result as $item) {
            $item->setPrimary($primary);
            $primary = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        if ($data instanceof Collection && !$data->isEmpty()) {
            foreach ($data as $item) {
                if (!$item instanceof PrimaryItem) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        $itemType = $this->getItemType($type);
        if ($itemType && class_exists($itemType)) {
            return in_array(self::PRIMARY_ITEM_TYPE, class_implements($itemType));
        }
        return false;
    }
}
