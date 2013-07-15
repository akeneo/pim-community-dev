<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Oro\Bundle\EntityConfigBundle\ConfigManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

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
                CONCAT(u.firstName, \' \'),
                CONCAT(u.lastName, \' \')
            ),
            CONCAT(\' - \', u.email)
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
                'label'       => 'Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
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
        $fieldAuthor->setFieldName('author');
        $fieldsCollection->add($fieldAuthor);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        $query->leftJoin('ea.user', 'u');
        $query->addSelect('ea', true);
        $query->addSelect('u', true);
        $query->addSelect($this->authorExpression . ' AS author', true);

//        $query->where('a.objectClass = :objectClass AND a.objectId = :objectId');
//        $query->setParameters(
//            array(
//                'objectClass' => $this->entityClass,
//                'objectId'    => $this->entityClassId
//            )
//        );

        return $query;
    }

    /**
     * Get distinct object classes
     *
     * @return array
     */
    /*
    protected function getObjectClassOptions()
    {
        $options = array();

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'a.objectClass')
            ->add('from', 'Oro\Bundle\EntityConfigBundle\Entity\ConfigLog a')
            ->distinct('a.objectClass');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array) $result as $value) {
            $options[$value['objectClass']] = current(array_reverse(explode('\\', $value['objectClass'])));
        }

        return $options;
    }*/
}
