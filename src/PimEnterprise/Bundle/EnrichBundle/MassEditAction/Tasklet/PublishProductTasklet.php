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

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /**
     * @param PublishedProductManager             $manager
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ValidatorInterface                  $validator
     * @param ObjectDetacherInterface             $objectDetacher
     * @param UserManager                         $userManager
     * @param TokenStorageInterface               $tokenStorage
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     *
     * @todo merge : remove properties $userManager and $tokenStorage in master branch. They are no longer used.
     */
    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        parent::__construct(
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher,
            $userManager,
            $tokenStorage
        );

        $this->authorizationChecker = $authorizationChecker;
        $this->pqbFactory = $pqbFactory;
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

                $violations = $this->validator->validate($entityWithFamily);
                $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithFamily);

                if (0 === $violations->count() && $isAuthorized) {
                    $this->stepExecution->incrementSummaryInfo('mass_published');
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $invalidEntitiesWithFamily[$index] = $entityWithFamily;

                    if (0 < $violations->count()) {
                        $this->addWarningMessage($violations, $entityWithFamily);
                    }
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
            $this->detachProducts($invalidEntitiesWithFamily);
            $this->manager->publishAll($productsPage);
            $this->detachProducts($productsPage);
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
