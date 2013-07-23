<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\FixedProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class AuditDatagridManager extends DatagridManager
{
    protected $configManager;

    /**
     * @var entityClass
     */
    public $entityClass;

    /**
     * @var entityClassId
     */
    public $entityClassId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @var string
     */
    protected $authorExpression =
        'CONCAT(
            CONCAT(
                CONCAT(user.firstName, \' \'),
                CONCAT(user.lastName, \' \')
            ),
            CONCAT(\' - \', user.email)
        )';

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Commit Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
                'show_column' => false,
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldAuthor = new FieldDescription();
        $fieldAuthor->setName('author');
        $fieldAuthor->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Author',
                'field_name'  => 'author',
                'expression'  => $this->authorExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldAuthor);

        $logDiffs = new FieldDescription();
        $logDiffs->setName('diffs');
        $logDiffs->setProperty(new FixedProperty('diffs', 'diffs'));
        $logDiffs->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Diffs',
                'field_name'  => 'diffs',
                'expression'  => 'diff',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDiffProperty = new TwigTemplateProperty(
            $logDiffs,
            'OroEntityConfigBundle:Audit:data.html.twig'
        );
        $logDiffs->setProperty($templateDiffProperty);
        $fieldsCollection->add($logDiffs);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('loggedAt');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Logged at',
                'field_name'  => 'loggedAt',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldCreated);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'loggedAt' => SorterInterface::DIRECTION_DESC
        );
    }

    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface|void
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->addSelect($this->authorExpression . ' AS author', true);

        $query->leftJoin('log.user', 'user');
        $query->leftJoin('log.diffs', 'diff');

        $query->where('diff.className = :className AND diff.fieldName IS NULL');
        $query->setParameter('className', $this->entityClass);

        return $query;
    }
}
