<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Product association datagrid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationDatagridManager extends FlexibleDatagridManager
{
    /**
     * @var ProductInterface $product
     */
    private $product;

    /**
     * @var Association $association
     */
    private $association;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

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
     * @param SecurityFacade $securityFacade
     */
    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * Set product
     *
     * @param ProductInterface $product
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @throws \LogicException
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        if (!$this->product) {
            throw new \LogicException('Product association datagrid manager has no configured product');
        }

        return $this->product;
    }

    /**
     * @param Association $association
     */
    public function setAssociation(Association $association = null)
    {
        $this->association = $association;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createProductAssociationField();
        $fieldsCollection->add($field);

        $identifier = $this->flexibleManager->getIdentifierAttribute();
        $field = $this->createFlexibleField($identifier);
        $fieldsCollection->add($field);

        $this->createFlexibleFilters($fieldsCollection);

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('created', 'Created at');
        $fieldsCollection->add($field);

        $field = $this->createDatetimeField('updated', 'Updated at');
        $fieldsCollection->add($field);
    }

    /**
     * It creates an editable checkbox to add/remove association to the edited product
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createProductAssociationField()
    {
        $field = new FieldDescription();
        $field->setName('has_association');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Has association'),
                'field_name'  => 'hasCurrentAssociation',
                'expression'  => $this->getHasAssociationExpression(),
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
        $result = parent::getFlexibleFieldOptions($attribute, $options);

        $result['show_filter'] = $attribute->getAttributeType() === 'pim_catalog_identifier';

        return $result;
    }

    /**
     * Get expression for assigned checkbox
     *
     * @return string
     */
    protected function getHasAssociationExpression()
    {
        if (null === $this->association) {
            return 0;
        }

        $hasAssociationExpression =
            'CASE WHEN ' .
            '(pa IS NOT NULL OR o.id IN (:data_in)) ' .
            'AND o.id NOT IN (:data_not_in) ' .
            'THEN true ELSE false END';

        return $hasAssociationExpression;
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
            $backendType = $attribute->getBackendType();
            if (in_array($backendType, $excludedBackend)) {
                continue;
            }

            if (!$attribute->isUseableAsGridColumn() && !$attribute->isUseableAsGridFilter()) {
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

        $familyExpr = '(CASE WHEN ft.label IS NULL THEN productFamily.code ELSE ft.label END)';
        $proxyQuery
            ->addSelect(sprintf('%s AS familyLabel', $familyExpr), true)
            ->addSelect($this->getHasAssociationExpression() . ' AS hasCurrentAssociation', true);

        $proxyQuery
            ->leftJoin($rootAlias .'.family', 'productFamily')
            ->leftJoin('productFamily.translations', 'ft', 'WITH', 'ft.locale = :localeCode')
            ->leftJoin(
                'PimCatalogBundle:ProductAssociation',
                'pa',
                'WITH',
                sprintf('pa.association = :association AND pa.owner = :product AND %s MEMBER OF pa.targets', $rootAlias)
            );

        $this->applyProductExclusionExpression($proxyQuery);

        $proxyQuery->setParameter('localeCode', $this->flexibleManager->getLocale());
        $proxyQuery->setParameter('product', $this->getProduct());
    }

    /**
     * Exclude the current product from the results
     *
     * @param ProxyQueryInterface $proxyQuery
     */
    protected function applyProductExclusionExpression(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        $proxyQuery->andWhere($proxyQuery->expr()->neq($rootAlias . '.id', $this->getProduct()->getId()));
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
            'scopeCode'   => $this->flexibleManager->getScope(),
            'association' => $this->association,
            'product'     => $this->getProduct()
        );
    }
}
