<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

/**
 * Job datagrid manager
 * A "job type" property is passed to the service to define if the grid must show import or export jobs
 * It allow to redefine other job type
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
     * {@inheritdoc}
     */
    public function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createTextField('code', 'Code');
        $fieldsCollection->add($field);

        $field = $this->createTextField('label', 'Label');
        $fieldsCollection->add($field);

        $field = $this->createTextField('connector', 'Connector');
        $fieldsCollection->add($field);

        $field = $this->createTextField('status', 'Status');
        $templateProperty = new TwigTemplateProperty($field, 'PimImportExportBundle:Export:_field-status.html.twig');
        $field->setProperty($templateProperty);
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
                'filterable'  => true
            )
        );

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
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->getQueryBuilder()->andWhere(sprintf('%s.type = :job_type', $query->getRootAlias()));
        $query->setParameter('job_type', $this->jobType);
    }
}
