<?php

namespace Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Product writer
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var VersionManager */
    protected $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool */
    protected $realTimeVersioning = true;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var BulkObjectDetacherInterface */
    protected $detacher;

    /**
     * Constructor
     *
     * @param VersionManager              $versionManager
     * @param BulkSaverInterface          $productSaver
     * @param BulkObjectDetacherInterface $detacher
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkObjectDetacherInterface $detacher
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver   = $productSaver;
        $this->detacher       = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'realTimeVersioning' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.realTimeVersioning.label',
                    'help'  => 'pim_connector.import.realTimeVersioning.help'
                ]
            ]
        ];
    }

    /**
     * Set real time versioning
     *
     * @param bool $realTime
     */
    public function setRealTimeVersioning($realTime)
    {
        $this->realTimeVersioning = $realTime;
    }

    /**
     * Is real time versioning
     *
     * @return bool
     */
    public function isRealTimeVersioning()
    {
        return $this->realTimeVersioning;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->versionManager->setRealTimeVersioning($this->realTimeVersioning);
        foreach ($items as $item) {
            $this->incrementCount($item);
        }

        $this->productSaver->saveAll($items, ['recalculate' => false]);
        $this->detacher->detachAll($items);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ProductInterface $product
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
