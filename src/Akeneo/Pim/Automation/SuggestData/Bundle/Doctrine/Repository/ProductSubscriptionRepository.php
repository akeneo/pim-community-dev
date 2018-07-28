<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var ObjectManager */
    private $em;

    /** @var string */
    private $className;

    /**
     * @param ObjectManager $em
     * @param string $className
     */
    public function __construct(ObjectManager $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscriptionInterface $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductAndSubscriptionId(
        ProductInterface $product,
        string $subscriptionId
    ): ?ProductSubscriptionInterface {
        $repository = $this->em->getRepository($this->className);

        return $repository->findOneBy(
            [
                'product'        => $product,
                'subscriptionId' => $subscriptionId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function existsForProductId(int $productId): bool
    {
        throw new \LogicException('Not yet implemented');
    }
}
