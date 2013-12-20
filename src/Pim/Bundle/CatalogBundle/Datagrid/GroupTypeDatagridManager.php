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

/**
 * Group type datagrid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeDatagridManager extends DatagridManager
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_group_type_edit', array('id')),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldsCollection->add($this->createCodeField());
        $fieldsCollection->add($this->createLabelField());
    }

    /**
     * Create code field
     *
     * @return FieldDescription
     */
    protected function createCodeField()
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

        return $field;
    }

    /**
     * Create label field
     *
     * @return FieldDescription
     */
    protected function createLabelField()
    {
        $field = new FieldDescription();
        $field->setName('label');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Label'),
                'field_name'  => 'groupTypeLabel',
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

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'pim_catalog_group_type_edit',
            'options'      => array(
                'label' => $this->translate('Edit'),
                'icon'  => 'edit',
                'link'  => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clickAction['options']['runOnRowClick'] = true;

        return array($clickAction, $editAction);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = 'g';
        $proxyQuery
            ->select($rootAlias)
            ->from('PimCatalogBundle:GroupType', $rootAlias);
        $labelExpr = sprintf(
            "(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)",
            $rootAlias
        );
        $proxyQuery
            ->addSelect(sprintf("%s AS groupTypeLabel", $labelExpr), true)
            ->addSelect('translation.label', true);
        $proxyQuery
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        $proxyQuery->setParameter('localeCode', $this->getCurrentLocale());
    }

    /**
     * Set the locale manager
     *
     * @param LocaleManager $localeManager
     *
     * @return GroupTypeDatagridManager
     */
    public function setLocaleManager(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;

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
