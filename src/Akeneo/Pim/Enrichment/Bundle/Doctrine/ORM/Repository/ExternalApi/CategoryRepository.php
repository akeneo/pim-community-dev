<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends EntityRepository implements ApiResourceRepositoryInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param EntityManager $entityManager
     * @param string $className
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        EntityManager $entityManager,
        $className,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($entityManager, $entityManager->getClassMetadata($className));

        $this->categoryRepository = $categoryRepository;
    }

    public function getIdentifierProperties()
    {
        return $this->categoryRepository->getIdentifierProperties();
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->categoryRepository->findOneByIdentifier($identifier);
    }

    /**
     * Find resources with offset > $offset and filtered by $criteria
     *
     * @param array{string: array{operator: string, value: mixed}[]} $searchFilters
     * @param array $orders
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function searchAfterOffset(array $searchFilters, array $orders, $limit, $offset)
    {
        $qb = $this->createQueryBuilder('r');
        $qb = $this->addFilters($qb, $searchFilters);

        foreach ($orders as $field => $sort) {
            $qb->addOrderBy(sprintf('r.%s', $field), $sort);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $searchFilters = []): int
    {
        try {
            $qb = $this->createQueryBuilder('r');
            $this->addFilters($qb, $searchFilters);

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
                    case '=':
                        if ('parent' === $property) {
                            $parentCategory = $this->createQueryBuilder('pr')
                                ->where('pr.code = :parent_code')
                                ->setParameter('parent_code', $criterion['value'])
                                ->getQuery()
                                ->getOneOrNullResult();

                            if (!$parentCategory) {
                                throw new \InvalidArgumentException(sprintf('Parent code %s does not exist.', $criterion['value']));
                            }

                            $qb->andWhere($qb->expr()->gt('r.left', $parentCategory->getLeft()));
                            $qb->andWhere($qb->expr()->lt('r.right', $parentCategory->getRight()));
                            $qb->andWhere($qb->expr()->eq('r.root', $parentCategory->getRoot()));
                        } else {
                            $qb->andWhere($qb->expr()->eq($field, $parameter));
                            $qb->setParameter($parameter, $criterion['value']);
                        }
                        break;
                    case 'IN':
                        $qb->andWhere($qb->expr()->in($field, $parameter));
                        $qb->setParameter($parameter, $criterion['value']);
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

        $validator = Validation::createValidator();
        $constraints = [
            'code' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => 'IN',
                        'message' => 'In order to search on category codes you must use "IN" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'array',
                            'message' => 'In order to search on category codes you must send an array of category codes as value, {{ type }} given.'
                        ]),
                        new Assert\All([
                            new Assert\Type('string')
                        ])
                    ],
                ])
            ]),
            'parent' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '=',
                        'message' => 'In order to search on category parent you must use "=" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'string',
                            'message' => 'In order to search on category parent you must send a parent code category as value, {{ type }} given.'
                        ]),
                    ],
                ])
            ]),
        ];
        $availableSearchFilters = array_keys($constraints);

        $exceptionMessage = '';
        foreach ($searchFilters as $property => $searchFilter) {
            if (!in_array($property, $availableSearchFilters)) {
                throw new \InvalidArgumentException(sprintf(
                    'Available search filters are "%s" and you tried to search on unavailable filter "%s"',
                    implode(', ', $availableSearchFilters),
                    $property
                ));
            }
            $violations = $validator->validate($searchFilter, $constraints[$property]);
            if (0 !== $violations->count()) {
                foreach ($violations as $violation) {
                    $exceptionMessage .= $violation->getMessage();
                }
            }
        }
        if ('' !== $exceptionMessage) {
            throw new \InvalidArgumentException($exceptionMessage);
        }
    }
}
