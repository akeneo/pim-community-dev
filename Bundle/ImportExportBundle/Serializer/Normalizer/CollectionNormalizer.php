<?php

namespace Oro\Bundle\ImportExportBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returned normalized data
     *
     * @param Collection $object object to normalize
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $result = array();

        foreach ($object as $item) {
            $serializedItem = $this->serializer->serialize($item, $format, $context);
            $result[] = $serializedItem;
        }

        return $result;
    }

    /**
     * Returns collection of denormalized data
     *
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return ArrayCollection
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!is_array($data)) {
            return new ArrayCollection();
        }
        $itemType = $this->getItemType($class);
        if (!$itemType) {
            return new ArrayCollection($data);
        }
        $result = new ArrayCollection();
        foreach ($data as $item) {
            $result->add($this->serializer->deserialize($item, $itemType, $format, $context));
        }
        return $result;
    }

    /**
     * @param string $class
     * @return string|null
     */
    protected function getItemType($class)
    {
        if (preg_match(
            '/^(Doctrine\\\Common\\\Collections\\\ArrayCollection|ArrayCollection)(<([\w_<>\\\]+)>)$/',
            $class,
            $matches)
        ) {
            return $matches[3];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return (bool)preg_match(
            '/^(Doctrine\\\Common\\\Collections\\\ArrayCollection|ArrayCollection)(<[\w_<>\\\]+>)?$/',
            $type
        );
    }
}
