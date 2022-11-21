<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * AttributeOption searchable repository
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionSearchableRepository implements SearchableRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $entityName;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param EntityManagerInterface       $entityManager
     * @param string                       $entityName
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        $entityName,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->entityManager       = $entityManager;
        $this->entityName          = $entityName;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @return AttributeOptionInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o')
            ->distinct()
            ->from($this->entityName, 'o')
            ->leftJoin('o.attribute', 'a')
            ->andWhere('a.code = :attributeCode')
            ->setParameter('attributeCode', $options['identifier']);

        if (isset($options['identifier']) && $this->isAttributeAutoSorted($options['identifier']) && isset($options['catalogLocale'])) {
            $qb
                ->addSelect('v.value AS HIDDEN value')
                ->leftJoin('o.optionValues', 'v', Expr\Join::WITH, 'v.locale = :localeCode')
                ->setParameter('localeCode', $options['catalogLocale'])
                ->orderBy('v.value')
                ->addOrderBy('o.code');
        } else {
            $qb
                ->leftJoin('o.optionValues', 'v')
                ->orderBy('o.sortOrder')
                ->addOrderBy('o.code');
        }

        if (!empty($search)) {
            $qb->andWhere('v.value LIKE :search OR o.code LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb = $this->applyQueryOptions($qb, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $options
     *
     * @return QueryBuilder
     */
    protected function applyQueryOptions(QueryBuilder $qb, array $options)
    {
        if (isset($options['identifiers']) && is_array($options['identifiers']) && !empty($options['identifiers'])) {
            $qb->andWhere('o.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (isset($options['locale']) && null !== $options['locale']) {
            $qb->andWhere('v.locale = :locale');
            $qb->setParameter('locale', $options['locale']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        return $qb;
    }

    /**
     * @param string $attributeIdentifier
     *
     * @return bool
     */
    protected function isAttributeAutoSorted($attributeIdentifier)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeIdentifier);

        return $attribute->getProperty('auto_option_sorting');
    }
}
