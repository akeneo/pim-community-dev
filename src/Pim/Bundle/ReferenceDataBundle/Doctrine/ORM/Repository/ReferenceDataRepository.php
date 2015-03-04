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
     * {@inheritdoc}
     */
    public function getOption($id, $collectionId = null, array $options = array())
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = array())
    {
        //TODO-CR: bad, because referential could need parameters for constructors
        //TODO-CR: maybe we should just rely on a "code" property...
        $referential = new $this->_entityName();
        $referential->getIdentifierProperties();

        $identifiers = array_map(
            function ($property) {
                return $this->getAlias() . '.' . $property;
            },
            $referential->getIdentifierProperties()
        );

        $qb = $this->createQueryBuilder('cr');
        $qb->select(sprintf('%s.id as id, CONCAT(%s) as text', $this->getAlias(), implode(", ' - ', ", $identifiers)));

        return [
            'results' => $qb->getQuery()->getArrayResult(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionLabel($referenceData, $dataLocale)
    {
        return $referenceData->getIdentifier();
    }

    /**
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
}
