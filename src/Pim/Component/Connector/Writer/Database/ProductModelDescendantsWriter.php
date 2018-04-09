<?php
declare(strict_types=1);

namespace Pim\Component\Connector\Writer\Database;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;

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

    /**
     * @param SaverInterface $descendantsSaver
     */
    public function __construct(SaverInterface $descendantsSaver)
    {
        $this->descendantsSaver = $descendantsSaver;
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
