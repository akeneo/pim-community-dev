<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface;

/**
 * Repository for reference data entities
 *
 * TODO-CR: should not implement OptionRepositoryInterface that comes from the UI
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepository extends EntityRepository implements
 ReferenceDataRepositoryInterface, OptionRepositoryInterface
{
    /**
     * TODO-CR: should be renamed or dropped if unsed
     *
     * {@inheritdoc}
     */
    public function getOption($id, $collectionId = null, array $options = array())
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * TODO-CR: should be renamed
     *
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = array())
    {
        $labelProperties = $this->getReferenceDataLabelProperties();

        $labels = array_map(
            function ($property) {
                return $this->getAlias() . '.' . $property;
            },
            $labelProperties
        );

        $labelSelectExpr = $labels[0];
        if (count($labelProperties) > 1) {
            $labelSelectExpr = sprintf('CONCAT(%s)', implode(", ' - ', ", $labels));
        }

        $qb = $this->createQueryBuilder('cr');
        $qb->select(sprintf('%s.id as id, %s as text', $this->getAlias(), $labelSelectExpr));

        return [
            'results' => $qb->getQuery()->getArrayResult(),
        ];
    }

    /**
     * TODO-CR: should be dropped
     *
     * {@inheritdoc}
     */
    public function getOptionLabel($referenceData, $dataLocale)
    {
        $labelsProperties = $this->getReferenceDataLabelProperties();
        $labels = [];

        foreach ($labelsProperties as $property) {
            $getter = 'get' . ucfirst($property);
            $labels [] = $referenceData->$getter();
        }

        return implode(' - ', $labels);
    }

    /**
     * TODO-CR: should be dropped
     *
     * {@inheritdoc}
     */
    public function getOptionId($referenceData)
    {
        return $referenceData->getId();
    }

    public function getAlias()
    {
        return 'cr';
    }

    /**
     * The list of label properties of the reference data
     *
     * @return array
     */
    private function getReferenceDataLabelProperties()
    {
        $referenceDataClass = $this->_entityName;

        return $referenceDataClass::getLabelProperties();
    }
}
