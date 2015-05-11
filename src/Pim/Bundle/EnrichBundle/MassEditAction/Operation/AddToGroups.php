<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends AbstractMassEditOperation
{
    /** @var ArrayCollection */
    protected $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * @param ArrayCollection $groups
     *
     * @return AddToGroups
     */
    public function setGroups(ArrayCollection $groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return ArrayCollection
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
        return [];
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
    public function getOperationAlias()
    {
        return 'add-to-groups';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $groups = $this->getGroups();

        return [
            [
                'field' => 'groups',
                'value' => $this->getGroupsCode($groups),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'add_product_value';
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'product';
    }

    /**
     * @param ArrayCollection $groups
     *
     * @return array
     */
    protected function getGroupsCode(ArrayCollection $groups)
    {
        return $groups->map(
            function (GroupInterface $group) {
                return $group->getCode();
            }
        )->toArray();
    }
}
