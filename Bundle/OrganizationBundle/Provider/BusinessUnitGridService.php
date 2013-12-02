<?php

namespace Oro\Bundle\OrganizationBundle\Provider;

use Doctrine\ORM\EntityManager;

class BusinessUnitGridService
{
    /** @var EntityManager */
    protected $em;

    /** @var array */
    protected $choices;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Return filter choices for owner grid column
     *
     * @return array
     */
    public function getOwnerChoices()
    {
        return $this->getChoices('name', 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit');
    }

    /**
     * Return filter choices for organization grid column
     *
     * @return array
     */
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
        $key = $entity . '|' . $field;
        if (!isset($this->choices[$key])) {
            $this->choices[$key] = $this->em
                ->getRepository('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit')
                ->getGridFilterChoices($field, $entity, $alias);
        }

        return $this->choices[$key];
    }
}
