<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\EntityConfigBundle\ConfigManager;

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
                'label'       => 'Diff Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldCommitId = new FieldDescription();
        $fieldCommitId->setName('commit_id');
        $fieldCommitId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Commit Id',
                'field_name'  => 'commit_id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldCommitId);

        $fieldCode = new FieldDescription();
        $fieldCode->setName('code');
        $fieldCode->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Property',
                'field_name'  => 'code',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldCode);

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


        $fieldDiff = new FieldDescription();
        $fieldDiff->setName('value_diff');
        $fieldDiff->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Diff(s)',
                'field_name'  => 'value_diff',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDiffProperty = new TwigTemplateProperty(
            $fieldDiff,
            'OroEntityBundle:Audit:data.html.twig'
        );
        $fieldDiff->setProperty($templateDiffProperty);
        $fieldsCollection->add($fieldDiff);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        $query->addSelect('diff', true);
        $query->addSelect('diff.diff AS value_diff', true);
        $query->addSelect('commit.id AS commit_id');
        $query->addSelect('commit');
        $query->addSelect($this->authorExpression . ' AS author', true);
        $query->addSelect('value_object.code as code');

        $query->innerJoin('diff.commit', 'commit');
        $query->leftJoin('commit.user', 'user');
        $query->innerJoin('commit.diffs', 'entity_diff', 'WITH', 'entity_diff.objectId = :objectId AND entity_diff.className = :objectClass');
        $query->leftJoin('OroEntityConfigBundle:ConfigValue', 'value_object', 'WITH', 'value_object.id = diff.objectId');

        $query->where('diff.className = \'Oro\\Bundle\\EntityConfigBundle\\Entity\\ConfigValue\'');
        $query->setParameters(
            array(
                'objectClass' => $this->entityClass,
                'objectId'    => $this->entityClassId
            )
        );

        return $query;
    }
}
