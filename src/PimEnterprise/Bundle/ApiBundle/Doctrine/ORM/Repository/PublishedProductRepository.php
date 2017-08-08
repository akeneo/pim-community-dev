<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\Api\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface as WorkflowProductRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedProductRepository extends EntityRepository implements PublishedProductRepositoryInterface
{
    /** @var WorkflowProductRepositoryInterface */
    private $publishedProductRepository;

    /**
     * @param EntityManager                      $em
     * @param string                             $className
     * @param WorkflowProductRepositoryInterface $publishedProductRepository
     */
    public function __construct(
        EntityManager $em,
        string $className,
        WorkflowProductRepositoryInterface $publishedProductRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->publishedProductRepository = $publishedProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->publishedProductRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['published_product'];
    }
}
