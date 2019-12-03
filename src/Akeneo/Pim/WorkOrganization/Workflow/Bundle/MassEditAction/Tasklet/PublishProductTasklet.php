<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Publish tasklet for products
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class PublishProductTasklet extends AbstractProductPublisherTasklet implements TaskletInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /**
     * @param PublishedProductManager             $manager
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ValidatorInterface                  $validator
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param EntityManagerClearerInterface       $cacheClearer
     */
    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerClearerInterface $cacheClearer
    ) {
        parent::__construct(
            $manager,
            $paginatorFactory,
            $validator
        );

        $this->authorizationChecker = $authorizationChecker;
        $this->pqbFactory = $pqbFactory;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $cursor = $this->getProductsCursor($jobParameters->get('filters'));
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $productsPage) {
            $invalidEntitiesWithFamily = [];
            foreach ($productsPage as $index => $entityWithFamily) {
                if (!$entityWithFamily instanceof ProductInterface) {
                    $invalidEntitiesWithFamily[$index] = $entityWithFamily;

                    continue;
                }

                $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithFamily);

                if ($isAuthorized) {
                    $this->stepExecution->incrementSummaryInfo('mass_published');
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $invalidEntitiesWithFamily[$index] = $entityWithFamily;

                    if (!$isAuthorized) {
                        $this->stepExecution->addWarning(
                            'pim_enrich.mass_edit_action.publish.message.error',
                            [],
                            new DataInvalidItem($entityWithFamily)
                        );
                    }
                }
            }

            $productsPage = array_diff_key($productsPage, $invalidEntitiesWithFamily);
            $this->manager->publishAll($productsPage);

            $this->cacheClearer->clear();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder(array $filters = []): ProductQueryBuilderInterface
    {
        $filters = array_map(function ($filter) {
            if ('id' === $filter['field']) {
                $filter['field'] = 'self_and_ancestor.id';
            }

            return $filter;
        }, $filters);

        $pqb = $this->pqbFactory->create(['filters' => $filters]);

        return $pqb;
    }
}
