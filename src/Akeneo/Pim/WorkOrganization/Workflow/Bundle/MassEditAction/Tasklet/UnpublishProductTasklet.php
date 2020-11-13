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

    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductQueryBuilderFactoryInterface $publishedPqbFactory,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
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
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $cursor = $this->getProductsCursor($jobParameters->get('filters'));
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $productsPage) {
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

    public function totalItems(): int
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $cursor = $this->getProductsCursor($jobParameters->get('filters'));

        return $cursor->count();
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder(array $filters = [])
    {
        return $this->publishedPqbFactory->create(['filters' => $filters]);
    }
}
