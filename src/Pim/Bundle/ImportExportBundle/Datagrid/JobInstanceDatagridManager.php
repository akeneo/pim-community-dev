<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * JobInstance datagrid manager
 * A "job type" property is passed to the service to define if the grid must show import or export jobs
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceDatagridManager extends DatagridManager
{
    /**
     * Define the job type
     *
     * @var string
     */
    protected $jobType;

    /**
     * Define the connector registry
     *
     * @var ConnectorRegistry
     */
    protected $connectorRegistry;

    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        // prepare route names
        $editLink   = sprintf('pim_importexport_%s_profile_edit', $this->jobType);
        $showLink   = sprintf('pim_importexport_%s_profile_show', $this->jobType);
        $deleteLink = sprintf('pim_importexport_%s_profile_remove', $this->jobType);

        return array(
            new UrlProperty('edit_link', $this->router, $editLink, array('id')),
            new UrlProperty('show_link', $this->router, $showLink, array('id')),
            new UrlProperty('delete_link', $this->router, $deleteLink, array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => sprintf('pim_importexport_%s_profile_show', $this->jobType),
            'options'      => array(
                'label'         => $this->translate('Show'),
                'link'          => 'show_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => sprintf('pim_importexport_%s_profile_edit', $this->jobType),
            'options'      => array(
                'label' => $this->translate('Edit'),
                'icon'  => 'edit',
                'link'  => 'edit_link'
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => sprintf('pim_importexport_%s_profile_remove', $this->jobType),
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        $launchAction = array(
            'name'         => 'launch',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => sprintf('pim_importexport_%s_profile_launch', $this->jobType),
            'options'      => array(
                'label' => $this->translate('Launch'),
                'icon'  => 'play',
                'link'  => 'show_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction, $launchAction);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createTextField('code', 'Code');
        $fieldsCollection->add($field);

        $field = $this->createTextField('label', 'Label');
        $fieldsCollection->add($field);

        $field = $this->createJobField('alias', 'Job');
        $fieldsCollection->add($field);

        $field = $this->createConnectorField();
        $fieldsCollection->add($field);

        // define the status field
        $field = $this->createStatusField();
        $fieldsCollection->add($field);
    }

    /**
     * Create a text field description (to avoid code duplication)
     *
     * @param string $code
     * @param string $label
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createTextField($code, $label)
    {
        $field = new FieldDescription();
        $field->setName($code);
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate($label),
                'field_name'  => $code,
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true
            )
        );

        return $field;
    }

    /**
     * Create job field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createJobField()
    {
        // create choices
        $jobs = array();
        $registryJobs = $this->getRegistryJobs();
        foreach ($registryJobs as $registryJob) {
            $jobs = array_merge($jobs, array_keys($registryJob));
        }
        $choices = array_unique($jobs);
        $choices = empty($choices) ? array() : array_combine($jobs, $jobs);

        // create field description
        $field = new FieldDescription();
        $field->setName('alias');
        $field->setOptions(
            array(
                'type'          => FieldDescriptionInterface::TYPE_TEXT,
                'label'         => $this->translate('Job'),
                'field_name'    => 'alias',
                'filter_type'   => FilterInterface::TYPE_CHOICE,
                'required'      => false,
                'sortable'      => true,
                'filterable'    => true,
                'show_filter'   => true,
                'field_options' => array(
                    'choices'  => $choices,
                    'multiple' => true
                )
            )
        );

        return $field;
    }

    /**
     * Get registry jobs
     *
     * @return \Oro\Bundle\BatchBundle\Connector\multitype:JobInterface
     */
    protected function getRegistryJobs()
    {
        return $this->connectorRegistry->getJobs($this->jobType);
    }

    /**
     * Create connector field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createConnectorField()
    {
        // create choices
        $connectors = array_keys($this->getRegistryJobs());
        $choices = empty($connectors) ? array() : array_combine($connectors, $connectors);

        // create field description
        $field = new FieldDescription();
        $field->setName('connector');
        $field->setOptions(
            array(
                'type'          => FieldDescriptionInterface::TYPE_TEXT,
                'label'         => $this->translate('Connector'),
                'field_name'    => 'connector',
                'filter_type'   => FilterInterface::TYPE_CHOICE,
                'required'      => false,
                'sortable'      => true,
                'filterable'    => true,
                'show_filter'   => true,
                'field_options' => array(
                    'choices'  => $choices,
                    'multiple' => true
                )
            )
        );

        return $field;
    }

    /**
     * Create the status field
     *
     * @return FieldDescriptionInterface
     */
    protected function createStatusField()
    {
        // create choices
        $choices = array(
            JobInstance::STATUS_READY => $this->translate('pim_import_export.status.'. JobInstance::STATUS_READY)
        );

        // create field description
        $field = new FieldDescription();
        $field->setName('status');
        $field->setOptions(
            array(
                'type'          => FieldDescriptionInterface::TYPE_TEXT,
                'label'         => $this->translate('Status'),
                'field_name'    => 'status',
                'filter_type'   => FilterInterface::TYPE_CHOICE,
                'required'      => false,
                'sortable'      => true,
                'filterable'    => true,
                'show_filter'   => true,
                'field_options' => array(
                    'choices'  => $choices,
                    'multiple' => true
                )
            )
        );

        // add specific rendering
        $templateProperty = new TwigTemplateProperty(
            $field,
            'PimImportExportBundle:JobProfile:_field-status.html.twig'
        );
        $field->setProperty($templateProperty);

        return $field;
    }

    /**
     * Set job type to show grid
     *
     * @param string $jobType
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    public function setJobType($jobType)
    {
        $this->jobType = $jobType;

        return $this;
    }

    /**
     * Set connector registry
     *
     * @param ConnectorRegistry $connectorRegistry
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    public function setConnectorRegistry(ConnectorRegistry $connectorRegistry)
    {
        $this->connectorRegistry = $connectorRegistry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->getQueryBuilder()->andWhere(sprintf('%s.type = :job_type', $query->getRootAlias()));
        $query->setParameter('job_type', $this->jobType);
    }
}
