<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeOptionRepository as OroAttributeOptionRepository;
use Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface;

/**
 * Repository for AttributeOption entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRepository extends OroAttributeOptionRepository implements OptionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOption($id, $collectionId = null, array $options = array())
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
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = array())
    {
        if (null === $collectionId) {
            throw new \InvalidArgumentException('Please supply attribute id as collectionId');
        }

        $qb = $this->createQueryBuilder('o')
            ->select('o.id, o.code, v.value AS label')
            ->leftJoin('o.optionValues', 'v', 'WITH', 'v.locale=:locale')
            ->where('o.attribute=:attribute')
            ->orderBy('o.sortOrder')
            ->setParameter('locale', $dataLocale)
            ->setParameter('attribute', $collectionId);
        if ($search) {
            $qb->andWhere('v.value like :search OR o.code LIKE :search')
                ->setParameter('search', "$search%");
        }

        $options = array();
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $options[] = array(
                'id'   => $row['id'],
                'text' => $row['label'] ?: sprintf('[%s]', $row['code'])
            );
        }

        return array(
            'results' => $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionLabel($object, $dataLocale)
    {
        foreach ($object->getOptionValues() as $value) {
            if ($dataLocale === $value->getLocale()) {
                return $value->getValue();
            }
        }

        return $object->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId($object)
    {
        return $object->getId();
    }
}
