<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetFamilyIdsNotUsedByProductsQueryInterface;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetFamilyIdsUsedByProductsQueryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\Type;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository implements ApiResourceRepositoryInterface
{
    public function __construct(
        protected EntityManager $entityManager,
        protected string $className,
        protected FamilyRepositoryInterface $familyRepository,
        protected GetFamilyIdsUsedByProductsQueryInterface $getFamilyIdsUsedByProductsQuery,
        protected GetFamilyIdsNotUsedByProductsQueryInterface $getFamilyIdsNotUsedByProductsQuery,
        private ValidatorInterface $validator
    ) {
        parent::__construct($entityManager, $entityManager->getClassMetadata($className));
    }

    public function getIdentifierProperties(): array
    {
        return $this->familyRepository->getIdentifierProperties();
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->familyRepository->findOneByIdentifier($identifier);
    }

    /**
     * Find resources with offset > $offset and filtered by $criteria
     *
     * @param array{string: array{operator: string, value: mixed}[]} $searchFilters
     *
     * @return array
     */
    public function searchAfterOffset(array $searchFilters, array $orders, $limit, $offset): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb = $this->addFilters($qb, $searchFilters);

        foreach ($orders as $field => $sort) {
            $qb->addOrderBy(sprintf('r.%s', $field), $sort);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
        $qb->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }


    /**
     * @param array $searchFilters {string: array{operator: string, value: mixed}[]}
     *
     * @return int
     */
    public function count(array $searchFilters = []): int
    {
        try {
            $qb = $this->createQueryBuilder('r');
            $qb = $this->addFilters($qb, $searchFilters);

            return (int) $qb
                ->select('COUNT(r.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (UnexpectedResultException $e) {
            return 0;
        }
    }

    protected function addFilters(QueryBuilder $qb, array $searchFilters): QueryBuilder
    {
        $this->validateSearchFilters($searchFilters);

        foreach ($searchFilters as $property => $searchFilter) {
            foreach ($searchFilter as $key => $criterion) {
                $parameter = sprintf(':%s_%s', $property, $key);
                $field = sprintf('r.%s', $property);
                switch ($criterion['operator']) {
                    case 'IN':
                        $qb->andWhere($qb->expr()->in($field, $parameter));
                        $qb->setParameter($parameter, $criterion['value']);
                        break;
                    case '>':
                        $qb->andWhere($qb->expr()->gt($field, $parameter));
                        $qb->setParameter($parameter, $criterion['value']);
                        break;
                    case '=':
                        if ('has_products' !== $property) {
                            throw new \InvalidArgumentException('Invalid operator for search query.');
                        }

                        $familyIds = $criterion['value'] ?
                            $this->getFamilyIdsUsedByProductsQuery->execute() :
                            $this->getFamilyIdsNotUsedByProductsQuery->execute();

                        $qb->andWhere($qb->expr()->in('r.id', ':family_ids'));
                        $qb->setParameter('family_ids', $familyIds);

                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid operator for search query.');
                }
            }
        }

        return $qb;
    }

    protected function validateSearchFilters(array $searchFilters): void
    {
        if (empty($searchFilters)) {
            return;
        }

        $constraints = [
            'code' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => 'IN',
                        'message' => 'In order to search on family codes you must use "IN" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Type([
                            'type' => 'array',
                            'message' => 'In order to search on family codes you must send an array of family codes as value, {{ givenType }} given.'
                        ]),
                        new Assert\All([
                            new Assert\Type('string')
                        ])
                    ],
                ])
            ]),
            'updated' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '>',
                        'message' => 'Searching on the "updated" property require the ">" (greater than) operator, {{ value }} given.',
                    ]),
                    'value' => new Assert\DateTime(['format' => \DateTime::ATOM]),
                ])
            ]),
            'has_products' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '=',
                        'message' => 'In order to search on family has_product you must use "=" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'bool',
                            'message' => 'The "has_products" filter requires a boolean value, and the submitted value is not.',
                        ]),
                    ],
                ]),
            ]),
        ];
        $availableSearchFilters = array_keys($constraints);

        $exceptionMessages = [];
        foreach ($searchFilters as $property => $searchFilter) {
            if (!in_array($property, $availableSearchFilters)) {
                throw new \InvalidArgumentException(sprintf(
                    'Available search filters are "%s" and you tried to search on unavailable filter "%s"',
                    implode(', ', $availableSearchFilters),
                    $property
                ));
            }
            $violations = $this->validator->validate($searchFilter, $constraints[$property]);
            foreach ($violations as $violation) {
                $exceptionMessages[] = $violation->getMessage();
            }
        }
        if (!empty($exceptionMessages)) {
            throw new \InvalidArgumentException(implode(' ', $exceptionMessages));
        }
    }
}
