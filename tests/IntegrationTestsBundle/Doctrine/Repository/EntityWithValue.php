<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Doctrine\Repository;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This repository don't raise "akeneo technical event". For instance, we can load fixture without indexing data
 * in ES. It is useful when we want to test a query function.
 *
 * TODO: this class should implement `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
 */
final class EntityWithValue implements SaverInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var NormalizerInterface */
    private $storageNormalizer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param NormalizerInterface    $storageNormalizer
     */
    public function __construct(EntityManagerInterface $entityManager, NormalizerInterface $storageNormalizer)
    {
        $this->entityManager = $entityManager;
        $this->storageNormalizer = $storageNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entityWithValues, array $option = []): void
    {
        if (!$entityWithValues instanceof EntityWithFamilyInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Expects a "%s", "%s" provided.',
                EntityWithFamilyInterface::class,
                ClassUtils::getClass($entityWithValues)
            ));
        }

        if ($entityWithValues instanceof EntityWithFamilyVariantInterface) {
            $values = $entityWithValues->getValuesForVariation();
        } else {
            $values = $entityWithValues->getValues();
        }

        $rawValues = $this->storageNormalizer->normalize($values, 'storage');
        $entityWithValues->setRawValues($rawValues);

        $this->entityManager->persist($entityWithValues);
        $this->entityManager->flush($entityWithValues);
    }
}
