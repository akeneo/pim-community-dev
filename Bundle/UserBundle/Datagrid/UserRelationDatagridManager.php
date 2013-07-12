<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Group;

abstract class UserRelationDatagridManager extends DatagridManager
{
    /**
     * @var string
     */
    protected $hasEntityExpression;

    /**
     * Create a column that represents relation of user with another entity
     *
     * @return FieldDescription
     */
    abstract protected function createUserRelationColumn();

    /**
     * Create a column that represents relation of user with another entity
     *
     * @return Role|Group
     */
    abstract protected function getRelatedEntity();

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldsCollection->add($this->createUserRelationColumn());

        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'ID',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_column' => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('firstName');
        $fieldFirstName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'First name',
                'field_name'  => 'firstName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldFirstName);

        $fieldLastName = new FieldDescription();
        $fieldLastName->setName('lastName');
        $fieldLastName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Last name',
                'field_name'  => 'lastName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldLastName);

        $fieldUsername = new FieldDescription();
        $fieldUsername->setName('username');
        $fieldUsername->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Username',
                'field_name'  => 'username',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUsername);

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Email',
                'field_name'  => 'email',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldEmail);
    }

    /**
     * @return string
     */
    protected function getHasEntityExpression()
    {
        if (null === $this->hasEntityExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getRelatedEntity()->getId()) {
                $this->hasEntityExpression =
                    "CASE WHEN " .
                    "(:entity MEMBER OF $entityAlias.groups OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasEntityExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }
        }

        return $this->hasEntityExpression;
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array('id' => SorterInterface::DIRECTION_ASC);
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn    = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);

        return array(
            'data_in'     => $dataIn,
            'data_not_in' => $dataNotIn,
        );
    }
}
