<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;

/**
 * Group datagrid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupDatagridManager extends DatagridManager
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var GroupManager
     */
    protected $groupManager;

    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_group_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_catalog_group_remove', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $this->createCodeField($fieldsCollection);
        $this->createLabelField($fieldsCollection);
        $this->createTypeField($fieldsCollection);
    }

    /**
     * Create code field
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createCodeField(FieldDescriptionCollection $fieldsCollection)
    {
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
    }

    /**
     * Create label field
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createLabelField(FieldDescriptionCollection $fieldsCollection)
    {
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
    }

    /**
     * Create group type field
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createTypeField(FieldDescriptionCollection $fieldsCollection)
    {
        $choices = $this->groupManager->getTypeChoices(false);
        $field = new FieldDescription();
        $field->setName('type');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Type'),
                'field_name'      => 'type',
                'filter_type'     => FilterInterface::TYPE_CHOICE,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'field_options'   => array('choices' => $choices),
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'pim_catalog_group_edit',
            'options'      => array(
                'label' => $this->translate('Edit'),
                'icon'  => 'edit',
                'link'  => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clickAction['options']['runOnRowClick'] = true;

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'pim_catalog_group_remove',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $proxyQuery
            ->select('g')
            ->from('Pim\Bundle\CatalogBundle\Model\Group', 'g');

        $groupLabelExpr = "(CASE WHEN translation.label IS NULL THEN g.code ELSE translation.label END)";
        $typeLabelExpr = "(CASE WHEN typTrans.label IS NULL THEN typ.code ELSE typTrans.label END)";

        $proxyQuery
            ->addSelect(sprintf("%s AS groupLabel", $groupLabelExpr), true)
            ->addSelect(sprintf("%s AS typeLabel", $typeLabelExpr), true)
            ->addSelect('translation.label', true);

        $proxyQuery
            ->leftJoin('g.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin('g.type', 'typ')
            ->leftJoin('typ.translations', 'typTrans', 'WITH', 'typTrans.locale = :localeCode');

        $this->applyJoinOnGroupType($proxyQuery);

        $proxyQuery->setParameter('localeCode', $this->getCurrentLocale());
    }

    /**
     * Apply join on group type
     * @param ProxyQueryInterface $proxyQuery
     */
    protected function applyJoinOnGroupType(ProxyQueryInterface $proxyQuery)
    {
        $joinExpr = $proxyQuery->expr()->neq('type.code', ':group');
        $proxyQuery
            ->innerJoin($proxyQuery->getRootAlias() .'.type', 'type', 'WITH', $joinExpr)
            ->setParameter('group', 'VARIANT');
    }

    /**
     * Set the locale manager
     *
     * @param LocaleManager $localeManager
     *
     * @return GroupDatagridManager
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
     * @return \Pim\Bundle\CatalogBundle\Datagrid\GroupDatagridManager
     */
    public function setGroupManager(GroupManager $groupManager)
    {
        $this->groupManager = $groupManager;

        return $this;
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
}
