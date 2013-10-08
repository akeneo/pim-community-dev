<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Product datagrid to link products to variant groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductDatagridManager extends FlexibleDatagridManager
{
    /**
     * @var VariantGroup $variantGroup
     */
    private $variantGroup;

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createAssignedField();
        $fieldsCollection->add($field);

        $identifier = $this->flexibleManager->getIdentifierAttribute();
        $field = $this->createFlexibleField($identifier);
        $fieldsCollection->add($field);

        foreach ($this->getVariantGroup()->getAttributes() as $attribute) {
            $field = $this->createFlexibleField($attribute);
            $fieldsCollection->add($field);
        }

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('created', 'Created at');
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('updated', 'Updated at');
        $fieldsCollection->add($field);
    }

    /**
     * It creates an editable checkbox to add/remove product to the edited variant
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createAssignedField()
    {
        $field = new FieldDescription();
        $field->setName('has_product');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Assigned'),
                'field_name'  => 'has_product',
                'nullable'    => false,
                'editable'    => true,
                'sortable'    => false,
                'filter_type' => false
            )
        );

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFlexibleFieldOptions(AbstractAttribute $attribute, array $options = array())
    {
        $result = parent::getFlexibleFieldOptions($attribute);

        $result['filterable']  = false;
        $result['show_filter'] = false;
        $result['sortable']    = $attribute->getAttributeType() === 'pim_catalog_identifier';

        return $result;
    }

    /**
     * Create family field description
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFamilyField()
    {
        $field = new FieldDescription();
        $field->setName('family');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Family'),
                'field_name'      => 'familyLabel',
                'expression'      => 'family',
                'filter_type'     => false,
                'required'        => false,
                'sortable'        => true
            )
        );

        return $field;
    }

    /**
     * Create a datetime field
     *
     * @param string $code
     * @param string $label
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createDatetimeField($code, $label)
    {
        $field = new FieldDescription();
        $field->setName($code);
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate($label),
                'field_name'  => $code,
                'filter_type' => false,
                'sortable'    => true
            )
        );

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        $familyExpr  = "(CASE WHEN ft.label IS NULL THEN productFamily.code ELSE ft.label END)";
        $proxyQuery
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS familyLabel", $familyExpr), true);

        $proxyQuery
            ->leftJoin($rootAlias .'.family', 'productFamily')
            ->leftJoin('productFamily.translations', 'ft', 'WITH', 'ft.locale = :localeCode');

        $this->applyVariantExpression($proxyQuery);

        // apply join clause for attributes
        $attributeIds = $this->getVariantGroup()->getAttributeIds();
        $exprAttrIn = $proxyQuery->expr()->in('v.attribute', $attributeIds);
        $proxyQuery
            ->leftJoin($rootAlias .'.values', 'v', 'WITH', $exprAttrIn)
            ->andWhere($proxyQuery->expr()->isNotNull('v.option'));


        $proxyQuery
            ->setParameter('localeCode', $this->flexibleManager->getLocale());
    }

    /**
     * Apply clauses to get all product not linked to a variant
     * and all the product linked to the asked variant
     *
     * @param ProxyQueryInterface $proxyQuery
     */
    protected function applyVariantExpression(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        $variantField = $rootAlias .'.variantGroup';
        $variantExpr  = $proxyQuery->expr()->isNull($variantField);
        $productIds   = $this->getVariantGroup()->getProductIds();

        if (!empty($productIds)) {
            $exprProductsIn = $proxyQuery->expr()->in($variantField, $productIds);
            $proxyQuery->orWhere($exprProductsIn, $variantExpr);
        } else {
            $proxyQuery->andWhere($variantExpr);
        }
    }

    /**
     * Set a variant group
     *
     * @param VariantGroup $variantGroup
     */
    public function setVariantGroup(VariantGroup $variantGroup)
    {
        $this->variantGroup = $variantGroup;

        $this->routeGenerator->setRouteParameters(
            array('id' => $this->variantGroup->getId())
        );
    }

    /**
     * Get the variant group
     *
     * @throws \LogicException
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\VariantGroup
     */
    public function getVariantGroup()
    {
        if (!$this->variantGroup) {
            throw new \LogicException('Datagrid manager has no configured Variant group');
        }

        return $this->variantGroup;
    }
}
