<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\Writer\Database\ProductWriter as BaseProductWriter;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Product writer
 *
 * @author Soulet Olivier <olivier.soulet@akeneo.com>
 *
 * @deprecated please use \PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter
 *             instead, will be removed in 2.1
 */
class ProductWriter extends BaseProductWriter
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param VersionManager                $versionManager
     * @param BulkSaverInterface            $productSaver
     * @param CacheClearerInterface         $cacheClearer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        CacheClearerInterface $cacheClearer,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($versionManager, $productSaver, $cacheClearer);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns true if user is owner of the product or if the product does not exist yet or if the token does not exist
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasPermissions(ProductInterface $product)
    {
        try {
            $hasRight = (null === $product->getId())
                ? true : $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $hasRight = true;
        }

        return $hasRight;
    }

    /**
     * {@inheritdoc}
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($this->hasPermissions($product)) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('proposal');
        }
    }
}
