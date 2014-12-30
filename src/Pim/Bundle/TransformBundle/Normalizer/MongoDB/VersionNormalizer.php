<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Pim\Bundle\VersioningBundle\Model\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a version object into a MongoDB object array
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @staticvar string */
    const FORMAT = "mongodb_document";

    /** @staticvar string */
    const MONGO_ID = '_id';

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /**
     * @param MongoObjectsFactory $mongoFactory
     */
    public function __construct(MongoObjectsFactory $mongoFactory)
    {
        $this->mongoFactory = $mongoFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof Version && self::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        if (null !== $version->getId()) {
            $data[self::MONGO_ID] = $this->mongoFactory->createMongoId($version->getId());
        } else {
            $data[self::MONGO_ID] = $this->mongoFactory->createMongoId();
        }

        $data['author']       = $version->getAuthor();
        $data['resourceName'] = $version->getResourceName();
        $data['resourceId']   = (string) $version->getResourceId();
        $data['snapshot']     = $version->getSnapshot();
        $data['changeset']    = $version->getChangeset();
        $data['context']      = $version->getContext();
        $data['version']      = $version->getVersion();
        $data['loggedAt']     = $this->normalizer->normalize($version->getLoggedAt(), self::FORMAT);
        $data['pending']      = $version->isPending();

        return $data;
    }
}
