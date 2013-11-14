<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Datagrid for associating a product to groups
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationGroupDatagridManager extends DatagridManager
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var GroupManager
     */
    private $groupManager;

    /**
     * @var ProductInterface $product
     */
    private $product;

    /**
     * @var integer $associationId
     */
    private $associationId = 0;

    /**
     * Set the locale manager
     *
     * @param LocaleManager $localeManager
     *
     * @return AssociationGroupDatagridManager
     */
    public function setLocaleManager(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;

        return $this;
    }

    /**
     * Set the group manager
     *
     * @param GroupManager $groupManager
     *
     * @return AssociationGroupDatagridManager
     */
    public function setGroupManager(GroupManager $groupManager)
    {
        $this->groupManager = $groupManager;

        return $this;
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
     * @param integer $associationId
     */
    public function setAssociationId($associationId)
    {
        $this->associationId = $associationId;
    }

    /**
     * Get product
     *
     * @throws \LogicException
     *
     * @return ProductInterface
     */
    protected function getProduct()
    {
        if (!$this->product) {
            throw new \LogicException('Product association datagrid manager has no configured product');
        }

        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createGroupAssociationField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('code');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Code'),
                'field_name'  => 'code',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('label');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Label'),
                'field_name'  => 'groupLabel',
                'expression'  => 'translation.label',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $field->setProperty(
            new TwigTemplateProperty($field, 'PimGridBundle:Rendering:_toString.html.twig')
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('type');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Type'),
                'field_name'      => 'groupType',
                'expression'      => 'type',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'multiple'        => false,
                'class'           => 'PimCatalogBundle:GroupType',
                'property'        => 'code',
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * It creates an editable checkbox to add/remove group association to the edited product
     *
     * @return FieldDescription
     */
    protected function createGroupAssociationField()
    {
        $field = new FieldDescription();
        $field->setName('has_association');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Has association'),
                'field_name'      => 'hasCurrentAssociation',
                'expression'      => $this->getHasAssociationExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'filter_by_where' => true
            )
        );

        return $field;
    }

    /**
     * Get expression for assigned checkbox
     *
     * @return string
     */
    protected function getHasAssociationExpression()
    {
        $expression =
            'CASE WHEN ' .
            '(pa IS NOT NULL OR o.id IN (:data_in)) ' .
            'AND o.id NOT IN (:data_not_in) ' .
            'THEN true ELSE false END';

        return $expression;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();
        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $proxyQuery
            ->addSelect(sprintf('%s AS groupLabel', $labelExpr), true)
            ->addSelect('translation.label', true)
            ->addSelect('type.code as groupType', true)
            ->addSelect($this->getHasAssociationExpression() . ' AS hasCurrentAssociation', true);

        $proxyQuery
            ->leftJoin($rootAlias . '.type', 'type')
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin(
                'PimCatalogBundle:ProductAssociation',
                'pa',
                'WITH',
                sprintf('pa.association = :association AND pa.owner = :product AND %s MEMBER OF pa.groups', $rootAlias)
            );

        $proxyQuery
            ->setParameter('localeCode', $this->getCurrentLocale())
            ->setParameter('product', $this->getProduct());
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn    = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);
        $this->associationId = !empty($additionalParameters['associationId']) ?
                        $additionalParameters['associationId'] : $this->associationId;

        return array(
            'data_in'     => $dataIn,
            'data_not_in' => $dataNotIn,
            'association' => $this->associationId,
            'product'     => $this->getProduct()
        );
    }

    /**
     * Get the current locale code from the locale manager
     *
     * @return string
     */
    protected function getCurrentLocale()
    {
        return $this->localeManager->getUserLocale()->getCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_association' => SorterInterface::DIRECTION_DESC,
            'code'            => SorterInterface::DIRECTION_ASC
        );
    }
}
