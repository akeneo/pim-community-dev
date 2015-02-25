<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\ReferenceDataRepositoryInterface;
use Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface;

/**
 * Repository for custom referential entities
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataOptionRepository extends EntityRepository implements
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
    public function getOptionLabel($customReferential, $dataLocale)
    {
        throw new \Exception('TBI');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId($customReferential)
    {
        return $customReferential->getId();
    }

    public function getAlias()
    {
        return 'cr';
    }
}
