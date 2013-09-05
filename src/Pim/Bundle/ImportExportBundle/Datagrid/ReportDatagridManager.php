<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Pim\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Report datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReportDatagridManager extends DatagridManager
{
    /**
     * Job type
     *
     * @var string
     */
    protected $jobType = null;

    /**
     * {@inheritdoc}
     */
    public function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $proxyQuery->innerJoin($proxyQuery->getRootAlias() .'.jobInstance', 'jobInstance');
        $proxyQuery->addSelect('jobInstance.code as jobCode');
        $proxyQuery->addSelect('jobInstance.label as jobLabel');
        $proxyQuery->addSelect('jobInstance.alias as jobAlias');
        $proxyQuery->addSelect($proxyQuery->getRootAlias() .'.exitCode as exitCode');

        if ($this->jobType !== null) {
            $proxyQuery->andWhere('jobInstance.type = :job_type');
            $proxyQuery->setParameter('job_type', $this->jobType);
        }
    }

    /**
     * Set job type
     *
     * @param string $jobType
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\ReportDatagridManager
     */
    public function setJobType($jobType)
    {
        $this->jobType = $jobType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createCodeField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('label');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Label'),
                'field_name'      => 'jobLabel',
                'sortable'        => false,
                'filterable'      => false,
                'show_filter'     => false
            )
        );
        $fieldsCollection->add($field);

        $field = $this->createJobField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('startTime');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Date'),
                'field_name'  => 'startTime',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('status');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Status'),
                'field_name'  => 'exitCode',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'field_options' => array(
                    'choices'  => array_combine(BatchStatus::getAllLabels(), BatchStatus::getAllLabels()),
                    'multiple' => true
                )
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('download_link', $this->router, 'pim_importexport_report_download', array('id')),
            new UrlProperty('show_link', $this->router, 'pim_importexport_report_show', array('id')),
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
            'acl_resource' => 'pim_importexport_report_show',
            'options'      => array(
                'label'         => $this->translate('Show'),
                'link'          => 'show_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $downloadLogAction = array(
            'name'         => 'download',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'pim_importexport_report_download',
            'options'      => array(
                'label'   => $this->translate('download'),
                'icon'    => 'download',
                'link'    => 'download_link',
                'backUrl' => true
            )
        );

        return array($clickAction, $downloadLogAction);
    }
    /**
     * Create status field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createCodeField()
    {
        $field = new FieldDescription();
        $field->setName('code');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Code'),
                'field_name'      => 'jobCode',
                'expression'      => 'jobInstance.id',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => false,
                'filterable'      => true,
                'show_filter'     => true,
                'class'           => 'PimBatchBundle:JobInstance',
                'property'        => 'code',
                'query_builder'   => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('j')->orderBy('j.code', 'ASC');
                },
                'filter_by_where' => true,
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
        $jobType = $this->jobType;
        $field = new FieldDescription();
        $field->setName('alias');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Job'),
                'field_name'      => 'jobAlias',
                'expression'      => 'jobInstance.id',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => false,
                'filterable'      => true,
                'show_filter'     => true,
                'class'           => 'PimBatchBundle:JobInstance',
                'property'        => 'alias',
                'query_builder'   => function (EntityRepository $repository) use ($jobType) {
                    $qb = $repository->createQueryBuilder('j')->orderBy('j.alias', 'ASC');
                    if ($jobType !== null) {
                        $qb->where('j.type = :job_type')
                        ->setParameter('job_type', $jobType);
                    }

                    return $qb;
                },
                'filter_by_where' => true,
            )
        );

        return $field;
    }
}
