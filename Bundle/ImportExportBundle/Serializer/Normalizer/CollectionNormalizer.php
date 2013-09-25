<?php

namespace Oro\Bundle\ImportExportBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     * @throws InvalidArgumentException
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
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
            $serializedItem = $this->serializer->normalize($item, $format, $context);
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
            $result->add($this->serializer->denormalize($item, $itemType, $format, $context));
        }
        return $result;
    }

    /**
     * @param string $class
     * @return string|null
     */
    protected function getItemType($class)
    {
        $collectionRegexp = '/^(Doctrine\\\Common\\\Collections\\\ArrayCollection|ArrayCollection)(<([\w_<>\\\]+)>)$/';

        if (preg_match($collectionRegexp, $class, $matches)) {
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
