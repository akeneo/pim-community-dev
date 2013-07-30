<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Pim\Bundle\BatchBundle\Connector\ConnectorRegistry;

use Pim\Bundle\BatchBundle\Entity\Job;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

/**
 * Job datagrid manager
 * A "job type" property is passed to the service to define if the grid must show import or export jobs
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobDatagridManager extends DatagridManager
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
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'     => FieldDescriptionInterface::TYPE_INTEGER,
                'required' => true,
            )
        );

        return array(
            new FieldProperty($fieldId),
            new UrlProperty('show_link', $this->router, sprintf('pim_ie_%s_show', $this->jobType), array('id')),
            new UrlProperty('delete_link', $this->router, sprintf('pim_ie_%s_remove', $this->jobType), array('id')),
            new UrlProperty('report_link', $this->router, sprintf('pim_ie_%s_report', $this->jobType), array('id')),
            // new UrlProperty('launch_link', $this->router, sprintf('pim_ie_%s_launch', $this->jobType), array('id'))
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
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => $this->translate('Show'),
                'link'          => 'show_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $showAction = array(
            'name'         => 'show',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Show'),
                'icon'    => 'edit',
                'link'    => 'show_link',
                'backUrl' => true
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        $reportAction = array(
            'name'         => 'report',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('View report'),
                'icon'    => 'picture',
                'link'    => 'report_link',
                'backUrl' => true
            )
        );

        $launchAction = array(
            'name'        => 'launch',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Launch'),
                'icon'    => 'play',
                'link'    => 'launch_link',
                'backUrl' => true
            )
        );

        return array($clickAction, $showAction, $deleteAction, $reportAction, $launchAction);
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
        $choices = array_combine($jobs, $jobs);

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
     * @return \Pim\Bundle\BatchBundle\Connector\multitype:JobInterface
     */
    protected function getRegistryJobs()
    {
        if ($this->jobType === Job::TYPE_EXPORT) {
            return $this->connectorRegistry->getExportJobs();
        } else {
            return $this->connectorRegistry->getImportJobs();
        }
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
        $choices = array_combine($connectors, $connectors);

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
            Job::STATUS_READY => $this->translate('pim_import_export.status.'. Job::STATUS_READY)
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
        $templateProperty = new TwigTemplateProperty($field, 'PimImportExportBundle:Job:_field-status.html.twig');
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
