<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Widget to display last import/export operations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LastOperationsWidget implements WidgetInterface
{
    /** @var JobExecutionManager */
    protected $manager;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param JobExecutionManager $manager
     * @param TranslatorInterface $translator
     */
    public function __construct(JobExecutionManager $manager, TranslatorInterface $translator)
    {
        $this->manager    = $manager;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'last_operations';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimDashboardBundle:Widget:last_operations.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $operations = $this->manager->getLastOperationsData([]);

        foreach ($operations as &$operation) {
            $operation['statusLabel'] = $this
                ->translator
                ->trans('pim_import_export.batch_status.' . $operation['status']);
            if ($operation['date'] instanceof \DateTime) {
                $operation['date'] = $operation['date']->format('U');
            }
        }

        return $operations;
    }
}
