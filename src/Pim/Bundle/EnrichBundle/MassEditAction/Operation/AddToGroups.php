<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToGroupsType;
use Pim\Component\Catalog\Model\GroupInterface;

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
     * @param string $jobInstanceCode
     */
    public function __construct($jobInstanceCode)
    {
        parent::__construct($jobInstanceCode);

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
        return AddToGroupsType::class;
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
