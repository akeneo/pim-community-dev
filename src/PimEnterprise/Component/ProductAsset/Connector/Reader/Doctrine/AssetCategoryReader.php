<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;

/**
 * Get asset categories
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetCategoryReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepository;

    /** @var bool Checks if all asset categories are sent to the processor */
    protected $isExecuted = false;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \ArrayIterator */
    protected $results;

    /**
     * @param CategoryRepositoryInterface $assetCategoryRepository
     */
    public function __construct(CategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->assetCategoryRepository = $assetCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->isExecuted) {
            $this->isExecuted = true;

            $this->results = $this->getResults();
        }

        if (null !== $result = $this->results->current()) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return \ArrayIterator
     */
    protected function getResults()
    {
        return new \ArrayIterator($this->assetCategoryRepository->getOrderedAndSortedByTreeCategories());
    }
}
