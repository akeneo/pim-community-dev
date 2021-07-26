<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Unpublish tasklet for published products
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class UnpublishProductTasklet extends AbstractProductPublisherTasklet implements TaskletInterface, TrackableTaskletInterface
{
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected ProductQueryBuilderFactoryInterface $publishedPqbFactory;
    protected EntityManagerClearerInterface $cacheClearer;
    private JobRepositoryInterface $jobRepository;
    protected JobStopper $jobStopper;
    protected int $batchSize;

    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductQueryBuilderFactoryInterface $publishedPqbFactory,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
        int $batchSize = 100
    ) {
        parent::__construct(
            $manager,
            $paginatorFactory,
            $validator
        );

        $this->authorizationChecker = $authorizationChecker;
        $this->publishedPqbFactory = $publishedPqbFactory;
        $this->cacheClearer = $cacheClearer;
        $this->jobRepository = $jobRepository;
        $this->jobStopper = $jobStopper;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $cursor = $this->getProductsCursor($jobParameters->get('filters'));

        $this->stepExecution->setTotalItems($cursor->count());
        $productsPages = $this->paginatePublishedProducts($cursor);
        foreach ($productsPages as $productsPage) {
            if ($this->jobStopper->isStopping($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);
                break;
            }

            $invalidProducts = [];
            foreach ($productsPage as $index => $product) {
                $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

                if ($isAuthorized) {
                    $this->stepExecution->incrementSummaryInfo('mass_unpublished');
                    $this->stepExecution->incrementProcessedItems();
                } else {
                    $invalidProducts[$index] = $product;
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $this->stepExecution->incrementProcessedItems();
                    $this->stepExecution->addWarning(
                        'pim_enrich.mass_edit_action.unpublish.message.error',
                        [],
                        new DataInvalidItem($product)
                    );
                }
            }

            $productsPage = array_diff_key($productsPage, $invalidProducts);
            $this->manager->unpublishAll($productsPage);

            $this->cacheClearer->clear();
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }

    private function paginatePublishedProducts(CursorInterface $cursor): \Iterator
    {
        $nextPublishedProducts = [];
        $count = 0;
        foreach ($cursor as $publishedProduct) {
            $nextPublishedProducts[] = $publishedProduct;
            $count++;
            if ($this->batchSize <= $count) {
                yield $nextPublishedProducts;
                $nextPublishedProducts = [];
                $count = 0;
            }
        }

        if (!empty($nextPublishedProducts)) {
            yield $nextPublishedProducts;
        }
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder(array $filters = [])
    {
        return $this->publishedPqbFactory->create(['filters' => $filters]);
    }
}
