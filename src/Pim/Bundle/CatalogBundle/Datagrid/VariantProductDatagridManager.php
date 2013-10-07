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
    protected $variantGroup;

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $identifier = $this->flexibleManager->getIdentifierAttribute();
        $field = $this->createFlexibleField($identifier);
        $fieldsCollection->add($field);

        foreach ($this->variantGroup->getAttributes() as $attribute) {
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

        $proxyQuery
            ->setParameter('localeCode', $this->flexibleManager->getLocale());
    }

    /**
     * Set a variant group
     *
     * @param VariantGroup $variantGroup
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\VariantProductDatagridManager
     */
    public function setVariantGroup(VariantGroup $variantGroup)
    {
        $this->variantGroup = $variantGroup;

        return $this;
    }
}
