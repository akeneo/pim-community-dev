<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Save all descendance of a product model.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelDescendantsWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var SaverInterface */
    protected $descendantsSaver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /**
     * @param SaverInterface                $descendantsSaver
     * @param EntityManagerClearerInterface $cacheClearer
     *
     * @todo @merge Remove $cacheClearer. It's not used anymore.
     */
    public function __construct(
        SaverInterface $descendantsSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->descendantsSaver = $descendantsSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $productModels)
    {
        foreach ($productModels as $productModel) {
            $this->descendantsSaver->save($productModel);
            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('process');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
