<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface as CatalogAttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * Attribute repository for the API
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository implements AttributeRepositoryInterface
{
    /** @var CatalogAttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param EntityManager                       $em
     * @param string                              $className
     * @param CatalogAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        EntityManager $em,
        $className,
        CatalogAttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->attributeRepository->findOneByIdentifier($identifier);
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
    public function count(array $searchFilters = [])
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

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->attributeRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        return $this->attributeRepository->getIdentifierCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaAttributeCodes()
    {
        return $this->attributeRepository->findMediaAttributeCodes();
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
                        'message' => 'In order to search on attribute codes you must use "IN" operator, {{ compared_value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'array',
                            'message' => 'In order to search on attribute codes you must send an array of attribute codes as value, {{ type }} given.'
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
                        'message' => 'Searching on the "updated" property require the ">" (greater than) operator, {{ compared_value }} given.',
                    ]),
                    'value' => new Assert\DateTime(['format' => \DateTimeInterface::ATOM]),
                ])
            ]),
            'type' => new Assert\All(
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => 'IN',
                        'message' => 'In order to search on attribute types you must use "IN" operator, {{ compared_value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'array',
                            'message' => 'In order to search on attribute types you must send an array of attribute types as value, {{ type }} given.'
                        ]),
                        new Assert\All([
                            new Assert\Type('string')
                        ])
                    ],
                ])
            ),
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
