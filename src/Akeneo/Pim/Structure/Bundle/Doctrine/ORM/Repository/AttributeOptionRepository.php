<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Repository for AttributeOption entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRepository extends EntityRepository implements
    AttributeOptionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOption($id, $collectionId = null, array $options = [])
    {
        if (null === $collectionId) {
            throw new \InvalidArgumentException('Please supply attribute id as collectionId');
        }
        $option = $this->find($id);

        return $option && ($collectionId == $option->getAttribute()->getId())
            ? $option
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = [])
    {
        if (null === $collectionId) {
            throw new \InvalidArgumentException('Please supply attribute id as collectionId');
        }

        $identifier = isset($options['type']) && 'code' === $options['type'] ? 'code' : 'id';

        $qb = $this->createQueryBuilder('o')
            ->select('o.id, o.code, v.value AS label, a.properties')
            ->leftJoin('o.optionValues', 'v', 'WITH', 'v.locale=:locale')
            ->leftJoin('o.attribute', 'a')
            ->where('o.attribute=:attribute')
            ->orderBy('o.sortOrder, o.code')
            ->setParameter('locale', $dataLocale)
            ->setParameter('attribute', $collectionId);
        if ($search) {
            $qb->andWhere('v.value like :search OR o.code LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if (isset($options['ids'])) {
            $qb
                ->andWhere(
                    $qb->expr()->in(sprintf('o.%s', $identifier), ':ids')
                )
                ->setParameter('ids', $options['ids']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        $results = [];
        $autoSorting = null;

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            if (null === $autoSorting && isset($row['properties']['auto_option_sorting'])) {
                $autoSorting = $row['properties']['auto_option_sorting'];
            }

            $isLabelBlank = (null === $row['label']) || ('' === $row['label']);
            $results[] = [
                'id'   => $row[$identifier],
                'text' => $isLabelBlank ? sprintf('[%s]', $row['code']) : $row['label'],
            ];
        }

        if ($autoSorting) {
            usort(
                $results,
                function ($first, $second) {
                    return strcasecmp($first['text'], $second['text']);
                }
            );
        }

        return [
            'results' => $results
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionLabel($object, $dataLocale)
    {
        foreach ($object->getOptionValues() as $value) {
            if ($dataLocale === $value->getLocale() && null !== $value->getValue() && '' !== $value->getValue()) {
                return $value->getValue();
            }
        }

        return sprintf('[%s]', $object->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId($object)
    {
        return $object->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        list($attributeCode, $optionCode) = explode('.', $code);

        return $this->createQueryBuilder('o')
            ->innerJoin('o.attribute', 'a')
            ->where('a.code=:attribute_code')
            ->andWhere('o.code=:option_code')
            ->setParameter('attribute_code', $attributeCode)
            ->setParameter('option_code', $optionCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findCodesByIdentifiers($code, array $optionCodes)
    {
        return $this->createQueryBuilder('o')
            ->select('o.code')
            ->innerJoin('o.attribute', 'a')
            ->where('a.code=:attribute_code')
            ->andWhere('o.code IN (:option_codes)')
            ->setParameter('attribute_code', $code)
            ->setParameter('option_codes', $optionCodes)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['attribute', 'code'];
    }
}
