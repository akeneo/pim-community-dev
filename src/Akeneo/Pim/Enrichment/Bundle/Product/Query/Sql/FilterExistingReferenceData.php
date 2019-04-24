<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManager;


/**
 * Query to fetch category codes by a given list of product identifiers.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExistingReferenceData
{
    /** @var EntityManager */
    private $entityManager;

    /** @var ReferenceDataRepositoryResolverInterface */
    private $repositoryResolver;

    public function __construct(
        EntityManager $entityManager,
        ReferenceDataRepositoryResolverInterface $repositoryResolver
    ) {
        $this->entityManager = $entityManager;
        $this->repositoryResolver = $repositoryResolver;
    }
    
    public function filter(AttributeInterface $attribute, array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
        $tableName = $this->entityManager->getClassMetadata($repository->getClassName())->getTableName();

        $sql = sprintf('SELECT code FROM %s WHERE code IN (?)', $tableName);

        return $this->entityManager->getConnection()->executeQuery(
            $sql,
            [$codes],
            [Connection::PARAM_STR_ARRAY]
        )->fetchAll(FetchMode::COLUMN);
    }
}
