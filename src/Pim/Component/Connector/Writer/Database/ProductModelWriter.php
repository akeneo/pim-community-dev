<?php
declare(strict_types=1);

namespace Pim\Component\Connector\Writer\Database;

use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Product model saver, define custom logic and options for product model saving
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var VersionManager */
    protected $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $productModelSaver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /**
     * @param VersionManager                $versionManager
     * @param BulkSaverInterface            $productModelSaver
     * @param EntityManagerClearerInterface $cacheClearer
     *
     * @todo @merge Remove $cacheClearer. It's not used anymore.
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->versionManager = $versionManager;
        $this->productModelSaver = $productModelSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
        foreach ($items as $productModel) {
            $action = $productModel->getId() ? 'process' : 'create';
            $this->stepExecution->incrementSummaryInfo($action);
        }

        $this->productModelSaver->saveAll($items);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
