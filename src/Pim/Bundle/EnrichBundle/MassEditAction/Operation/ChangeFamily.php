<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

/**
 * Mass edit operation to change the family of products
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamily extends AbstractMassEditOperation
{
    /** @var FamilyInterface $family The family to change the product family to */
    protected $family;

    /**
     * @param FamilyInterface $family
     *
     * @return ChangeFamily
     */
    public function setFamily(FamilyInterface $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_change_family';
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
    public function getItemsName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'change-family';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $family = $this->getFamily();

        return [
            [
                'field' => 'family',
                'value' => null !== $family ? $family->getCode() : null,
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
}
