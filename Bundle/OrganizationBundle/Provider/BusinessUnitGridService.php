<?php

namespace Oro\Bundle\OrganizationBundle\Provider;

use Doctrine\ORM\EntityManager;

class BusinessUnitGridService
{
    /** @var EntityManager */
    protected $em;

    /** @var array */
    protected $choices;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getOwnerChoices()
    {
        return $this->getChoices('name', 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit');
    }

    public function getOrganizationChoices()
    {
        return $this->getChoices('name', 'Oro\Bundle\OrganizationBundle\Entity\Organization', 'o');
    }

    /**
     * @param string $field
     * @param string $entity
     * @param string $alias
     *
     * @return array
     */
    protected function getChoices($field, $entity, $alias = 'bu')
    {
        if (!isset($this->choices[$field])) {
            $options = array();

            $result = $this->em->createQueryBuilder()
                ->add('select', 'bu.' . $field)
                ->add('from', $entity . ' ' . $alias)
                ->distinct($alias . '.' . $field)
                ->getQuery()
                ->getArrayResult();

            foreach ((array) $result as $value) {
                $options[$value[$field]] = current(
                    array_reverse(
                        explode('\\', $value[$field])
                    )
                );
            }

            $this->choices[$field] = $options;
        }

        return $this->choices[$field];
    }
}
