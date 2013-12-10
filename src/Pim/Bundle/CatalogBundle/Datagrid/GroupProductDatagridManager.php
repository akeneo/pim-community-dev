<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Sorter\SorterInterface;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Product datagrid to link products to groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProductDatagridManager extends FlexibleDatagridManager
{
    /**
     * @var Group $group
     */
    private $group;

    /**
     * @var string
     */
    protected $hasProductExpression;

    /**
     * Define constructor to add new price type
     */
    public function __construct()
    {
        self::$typeMatches['prices'] = array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_CURRENCY
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createProductRelationField();
        $fieldsCollection->add($field);

        $identifier = $this->flexibleManager->getIdentifierAttribute();
        $field = $this->createFlexibleField($identifier);
        $fieldsCollection->add($field);

        $field = $this->createAttributeAsLabelField();
        $fieldsCollection->add($field);

        if ($this->group->getType()->isVariant()) {
            foreach ($this->getGroup()->getAttributes() as $attribute) {
                $field = $this->createFlexibleField($attribute);
                $fieldsCollection->add($field);
            }
        }

        $this->createFlexibleFilters($fieldsCollection);

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('created', 'Created at');
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('updated', 'Updated at');
        $fieldsCollection->add($field);
    }

    /**
     * It creates an editable checkbox to add/remove product to the edited group
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createProductRelationField()
    {
        $field = new FieldDescription();
        $field->setName('has_product');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Has product'),
                'field_name'      => 'hasCurrentProduct',
                'expression'      => $this->getHasProductExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'filter_by_where' => true,
                'show_filter'     => true
            )
        );

        return $field;
    }

    /**
     * Create attribute as label column
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createAttributeAsLabelField()
    {
        $field = new FieldDescription();
        $field->setName('attributeAsLabel');
        $field->setOptions(
            array(
                'type'              => FieldDescriptionInterface::TYPE_TEXT,
                'label'             => $this->translate('Label'),
                'field_name'        => 'label',
                'nullable'          => false,
                'sortable'          => false,
                'filter_type'       => false
            )
        );

        return $field;
    }

    /**
     * @return AbstractAttribute[]
     */
    protected function getFlexibleAttributes()
    {
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            $attributes = $attributeRepository->findAllWithTranslations();
            $this->attributes = array();
            /** @var $attribute AbstractAttribute */
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFlexibleFieldOptions(AbstractAttribute $attribute, array $options = array())
    {
        $result = parent::getFlexibleFieldOptions($attribute, $options);

        $result['show_filter'] = $attribute->getAttributeType() === 'pim_catalog_identifier';

        $result['show_column'] = ($this->getGroup()->getAttributes()->contains($attribute))
            ? true : $attribute->getAttributeType() === 'pim_catalog_identifier';

        return $result;
    }

    /**
     * Get expression for assigned checkbox
     *
     * @return string
     */
    protected function getHasProductExpression()
    {
        if (null === $this->hasProductExpression) {

            $this->hasProductExpression =
                "CASE WHEN " .
                "(:group MEMBER OF o.groups OR o.id IN (:data_in)) AND ".
                "o.id NOT IN (:data_not_in) ".
                "THEN true ELSE false END";
        }

        return $this->hasProductExpression;
    }

    /**
     * Create flexible filters when attributes are defined as filterable
     * and are not already in the fields collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createFlexibleFilters(FieldDescriptionCollection $fieldsCollection)
    {
        $excludedBackend = array(
            AbstractAttributeType::BACKEND_TYPE_MEDIA
        );

        foreach ($this->getFlexibleAttributes() as $attribute) {
            if (!$attribute->isUseableAsGridFilter()) {
                continue;
            }

            if (in_array($attribute->getBackendType(), $excludedBackend)) {
                continue;
            }

            if ($this->getGroup()->getAttributes()->contains($attribute)) {
                continue;
            }

            $field = $this->createFlexibleField($attribute);
            $fieldsCollection->add($field);
        }
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
                'expression'      => 'productFamily',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'multiple'        => true,
                'class'           => 'PimCatalogBundle:Family',
                'property'        => 'label',
                'filter_by_where' => true,
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
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false
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
            ->addSelect(sprintf("%s AS familyLabel", $familyExpr), true)
            ->addSelect($this->getHasProductExpression() .' AS hasCurrentProduct', true);

        $proxyQuery
            ->leftJoin($rootAlias .'.family', 'productFamily')
            ->leftJoin('productFamily.translations', 'ft', 'WITH', 'ft.locale = :localeCode')
            ->leftJoin($rootAlias .'.values', 'values')
            ->leftJoin('values.options', 'valueOptions')
            ->leftJoin('values.prices', 'valuePrices');

        $this->applyAttributeExpression($proxyQuery);

        $proxyQuery
            ->setParameter('localeCode', $this->flexibleManager->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn    = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);

        return array(
            'data_in'     => $dataIn,
            'data_not_in' => $dataNotIn,
            'group'       => $this->getGroup(),
        );
    }

    /**
     * Apply clause to get only product with values for each attributes
     * of the variant group
     *
     * @param ProxyQueryInterface $proxyQuery
     */
    protected function applyAttributeExpression(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        foreach ($this->getGroup()->getAttributes() as $attribute) {
            $valueField = sprintf('v_%s', $attribute->getId());
            $valueExpr = $valueField .'.attribute = '. $attribute->getId().' AND '
                .($proxyQuery->expr()->isNotNull($valueField.'.option'));
            $proxyQuery
                ->innerJoin(
                    $rootAlias .'.values',
                    $valueField,
                    'WITH',
                    $valueExpr
                );
        }
    }

    /**
     * Set a group
     *
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        $this->routeGenerator->setRouteParameters(
            array('id' => $this->group->getId())
        );
    }

    /**
     * Get the group
     *
     * @throws \LogicException
     *
     * @return Group
     */
    protected function getGroup()
    {
        if (!$this->group) {
            throw new \LogicException('Datagrid manager has no configured group');
        }

        return $this->group;
    }

    /**
     * {@inheritdoc}
     *
     * This method is overriden to prevent against the use of parameters
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_product' => SorterInterface::DIRECTION_DESC,
            $this->flexibleManager->getIdentifierAttribute()->getCode() => SorterInterface::DIRECTION_ASC
        );
    }
}
