<?php

namespace Akeneo\Test\IntegrationTestsBundle\Fixture\Saver;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValue
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
     * @param $entityWithValues
     */
    public function save(EntityWithValuesInterface $entityWithValues): void
    {
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