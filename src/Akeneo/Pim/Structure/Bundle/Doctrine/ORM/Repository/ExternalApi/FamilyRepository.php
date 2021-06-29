<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
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
class FamilyRepository extends EntityRepository implements ApiResourceRepositoryInterface
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    public function __construct(
        EntityManager $entityManager,
        $className,
        FamilyRepositoryInterface $familyRepository
    ) {
        parent::__construct($entityManager, $entityManager->getClassMetadata($className));

        $this->familyRepository = $familyRepository;
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
                        break;
                    case '>':
                        $qb->andWhere($qb->expr()->gt($field, $parameter));
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid operator for search query.');
                }
                $qb->setParameter($parameter, $criterion['value']);
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
                        'message' => 'In order to search on family codes you must use "IN" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'array',
                            'message' => 'In order to search on family codes you must send an array of family codes as value, {{ type }} given.'
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
            $violations = $validator->validate($searchFilter, $constraints[$property]);
            foreach ($violations as $violation) {
                $exceptionMessages[] = $violation->getMessage();
            }
        }
        if (!empty($exceptionMessages)) {
            throw new \InvalidArgumentException(implode(' ', $exceptionMessages));
        }
    }
}
