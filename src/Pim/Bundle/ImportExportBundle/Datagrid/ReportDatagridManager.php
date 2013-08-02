<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Pim\Bundle\BatchBundle\Job\BatchStatus;

use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

/**
 * Report datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('code');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Code'),
                'field_name'      => 'jobCode',
                'expression'      => 'job.id',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => false,
                'filterable'      => true,
                'show_filter'     => true,
                'class'           => 'PimBatchBundle:Job',
                'property'        => 'code',
                'query_builder'   => function (EntityRepository $er) {
                    return $er->createQueryBuilder('j')
                        ->orderBy('j.code', 'ASC');
                },
                'filter_by_where' => true,
            )
        );
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

        $field = new FieldDescription();
        $field->setName('alias');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Job'),
                'field_name'      => 'jobAlias',
                'expression'      => 'job.id',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => false,
                'filterable'      => true,
                'show_filter'     => true,
                'class'           => 'PimBatchBundle:Job',
                'property'        => 'alias',
                'query_builder'   => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('j')->orderBy('j.alias', 'ASC');
                    if ($this->jobType !== null) {
                        $qb->where('j.type = :job_type')
                           ->setParameter('job_type', $this->jobType);
                    }

                    return $qb;
                },
                'filter_by_where' => true,
            )
        );
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
                    'choices'  => array_combine(BatchStatus::$statusLabels, BatchStatus::$statusLabels),
                    'multiple' => true
                )
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $proxyQuery->innerJoin($proxyQuery->getRootAlias() .'.job', 'job');
        $proxyQuery->addSelect('job.code as jobCode');
        $proxyQuery->addSelect('job.label as jobLabel');
        $proxyQuery->addSelect('job.alias as jobAlias');
        $proxyQuery->addSelect($proxyQuery->getRootAlias() .'.exitCode as exitCode');

        if ($this->jobType !== null) {
            $proxyQuery->andWhere('job.type = :job_type');
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
}
