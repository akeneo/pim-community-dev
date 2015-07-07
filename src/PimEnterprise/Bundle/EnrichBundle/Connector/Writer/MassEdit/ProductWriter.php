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

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Connector\Writer\Doctrine\ProductWriter as BaseProductWriter;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product writer
 *
 * @author Soulet Olivier <olivier.soulet@akeneo.com>
 */
class ProductWriter extends BaseProductWriter
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param MediaManager                $mediaManager
     * @param VersionManager              $versionManager
     * @param BulkSaverInterface          $productSaver
     * @param BulkObjectDetacherInterface $detacher
     * @param SecurityContextInterface    $securityContext
     */
    public function __construct(
        MediaManager $mediaManager,
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkObjectDetacherInterface $detacher,
        SecurityContextInterface $securityContext
    ) {
        BaseProductWriter::__construct($mediaManager, $versionManager, $productSaver, $detacher);

        $this->securityContext = $securityContext;
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
        if (null === $product->getId() || null === $this->securityContext->getToken()) {
            $hasRight = true;
        } else {
            $hasRight = $this->securityContext->isGranted(Attributes::OWN, $product);
        }

        return $hasRight;
    }

    /**
     * {@inheritdoc}
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($this->hasPermissions($product)) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('proposal');
        }
    }
}
