<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

/**
 * Operation to add products to variant groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroup extends AbstractMassEditOperation implements
    ConfigurableOperationInterface,
    BatchableOperationInterface
{
    /** @var GroupInterface */
    protected $group;

    /**
     * Set group
     *
     * @param GroupInterface $group
     */
    public function setGroup(GroupInterface $group)
    {
        $this->group = $group;
    }

    /**
     * Get group
     *
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
        return 'pim_enrich_mass_add_to_variant_group';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'add-to-variant-group';
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig()
    {
        return addslashes(
            json_encode(
                [
                    'filters' => $this->getFilters(),
                    'actions' => $this->getActions(),
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return [
            [
                'field' => 'variant_group',
                'value' => $this->getGroup()->getCode(),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'update_product_value';
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'product';
    }
}
