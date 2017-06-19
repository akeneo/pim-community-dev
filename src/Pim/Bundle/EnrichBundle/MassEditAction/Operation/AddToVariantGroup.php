<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToVariantGroupType;
use Pim\Component\Catalog\Model\GroupInterface;

/**
 * Operation to add products to variant groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroup extends AbstractMassEditOperation
{
    /** @var GroupInterface */
    protected $group;

    /**
     * @param GroupInterface $group
     */
    public function setGroup(GroupInterface $group)
    {
        $this->group = $group;
    }

    /**
     * @return GroupInterface
     */
    public function getGroup()
    {
        return $this->group;
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
        return AddToVariantGroupType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'add-to-variant-group';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return [
                'field' => 'variant_group',
                'value' => $this->getGroup()->getCode(),
        ];
    }
}
