<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends AbstractMassEditAction
{
    /**
     * @var GroupRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * @var ArrayCollection $groups
     */
    protected $groups;

    /**
     * Constructor
     *
     * @param GroupRepository            $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository   = $groupRepository;
        $this->groups            = new ArrayCollection();
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
        $groups = $this->groupRepository->findAll();

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
     *
     * TODO: Check with MongoDB implementation
     */
    public function perform()
    {
        foreach ($this->products as $product) {
            foreach ($this->getGroups() as $group) {
                $group->addProduct($product);
            }
        }
    }
}
