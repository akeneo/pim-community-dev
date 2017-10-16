<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * Triggers the computation of the completeness for all products belonging to a family that has been updated by calling
 * save on them.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeCompletenessOfProductsFamily implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /**
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param SaverInterface                      $productSaver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        SaverInterface $productSaver
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyCode = $jobParameters->get('family_code');

        $products = $this->findProductsForFamily($familyCode);
        foreach ($products as $product) {
            $this->productSaver->save($product);
        }
    }

    /**
     * @param string $familyCode
     *
     * @return CursorInterface
     */
    private function findProductsForFamily(string $familyCode): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$familyCode]);

        return $pqb->execute();
    }
}
