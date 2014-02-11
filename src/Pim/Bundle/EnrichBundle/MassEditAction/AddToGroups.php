<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends AbstractMassEditAction
{
    /** @var ArrayCollection */
    protected $groups;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->groups        = new ArrayCollection();
        $this->entityManager = $entityManager;
    }

    /**
     * Set groups
     *
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = new ArrayCollection($groups);
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        $groups = $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findAll();

        return array(
            'groups' => $groups,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_add_to_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function perform(QueryBuilder $qb)
    {
        $products = $qb->getQuery()->getResult();
        foreach ($products as $product) {
            foreach ($this->getGroups() as $group) {
                $group->addProduct($product);
            }
        }
    }
}
