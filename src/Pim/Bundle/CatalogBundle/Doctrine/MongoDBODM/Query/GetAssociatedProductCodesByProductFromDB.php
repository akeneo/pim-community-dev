<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Query;

use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get associated product codes by product.
 * We inject the container to avoid a circular reference :/
 */
class GetAssociatedProductCodesByProductFromDB implements GetAssociatedProductCodesByProduct
{
    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $entityClass;

    public function __construct(ContainerInterface $container, $entityClass)
    {
        $this->container = $container;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodes($productId, $associationTypeId)
    {
        $documentManager = $this->container->get('doctrine.odm.mongodb.document_manager');

        $result = $documentManager->createQueryBuilder($this->entityClass)
            ->hydrate(false)
            ->field('associations.associationType')->equals($associationTypeId)
            ->field('_id')->equals($productId)
            ->select('associations')
            ->getQuery()
            ->execute()
            ->toArray();

        if (empty($result)) {
            return [];
        }

        $associatedProductIds = [];
        foreach ($result[$productId]['associations'] as $association) {
            if ($association['associationType'] === $associationTypeId) {
                foreach ($association['products'] as $associatedProduct) {
                    $associatedProductIds[] = (string) $associatedProduct['$id'];
                }
            }
        }

        if (empty($associatedProductIds)) {
            return [];
        }

        $attributeRepository = $this->container->get('pim_catalog.repository.attribute');
        $identifierCode = $attributeRepository->getIdentifierCode();

        $associations = $documentManager->createQueryBuilder($this->entityClass)
            ->hydrate(false)
            ->field('_id')->in($associatedProductIds)
            ->select('normalizedData.' . $identifierCode)
            ->sort('normalizedData.' . $identifierCode)
            ->getQuery()
            ->execute()
            ->toArray();

        $associationCodes = [];
        foreach ($associations as $association) {
            $associationCodes[] = $association['normalizedData'][$identifierCode];
        }

        return $associationCodes;
    }
}
