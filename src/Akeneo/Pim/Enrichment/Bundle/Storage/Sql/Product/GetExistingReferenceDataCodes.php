<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes as GetExistingReferenceDataCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManager;

/**
 * Query to fetch only the existing reference data
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingReferenceDataCodes implements GetExistingReferenceDataCodesInterface
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

    public function fromReferenceDataNameAndCodes(string $referenceDataName, array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        $repository = $this->repositoryResolver->resolve($referenceDataName);
        $tableName = $this->entityManager->getClassMetadata($repository->getClassName())->getTableName();

        $sql = sprintf('SELECT code FROM %s WHERE code IN (:codes) ORDER BY FIELD(code, :codes)', $tableName);

        return $this->entityManager->getConnection()->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(FetchMode::COLUMN);
    }
}
