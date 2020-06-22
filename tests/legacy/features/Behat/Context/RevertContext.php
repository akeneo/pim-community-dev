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

namespace PimEnterprise\Behat\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\ProductRevert\Exception\ConstraintViolationsException;
use Akeneo\Pim\WorkOrganization\ProductRevert\Reverter\ProductReverter;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Behat\Behat\Context\Context;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class RevertContext implements Context
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var ProductReverter */
    private $productReverter;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param VersionRepositoryInterface            $versionRepository
     * @param ProductReverter                       $productReverter
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        VersionRepositoryInterface $versionRepository,
        ProductReverter $productReverter
    ) {
        $this->productRepository = $productRepository;
        $this->versionRepository = $versionRepository;
        $this->productReverter = $productReverter;
    }

    /**
     * Reverts a product to its previous version.
     *
     * VersionRepositoryInterface::getLogEntries returns the versions as an array
     * sorted from the newest to the oldest.
     * So the previous version is the second one of the array, that we retrieved
     * with the "next" function.
     *
     * @param string $productIdentifier
     *
     * @throws ConstraintViolationsException
     *
     * @When the product :productIdentifier is reverted to the previous version
     * @When the variant product :productIdentifier is reverted to the previous version
     */
    public function revertProductToThePreviousVersion(string $productIdentifier): void
    {
        $product = $this->findProduct($productIdentifier);
        $versions = $this->versionRepository->getLogEntries(
            ClassUtils::getClass($product),
            $product->getId()
        );

        $versionsCount = count($versions);
        if (2 > $versionsCount) {
            throw new \InvalidArgumentException(sprintf(
                'At least 2 versions are needed to perform a revert, product "%s" contains %d',
                $productIdentifier,
                $versionsCount
            ));
        }

        $previousVersion = next($versions);

        $this->productReverter->revert($previousVersion);
    }

    /**
     * @param string $identifier
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductInterface
     */
    private function findProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(sprintf('The product "%s" does not exist.', $identifier));
        }

        return $product;
    }
}
