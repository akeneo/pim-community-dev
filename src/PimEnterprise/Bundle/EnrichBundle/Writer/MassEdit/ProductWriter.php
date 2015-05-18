<?php

namespace PimEnterprise\Bundle\EnrichBundle\Writer\MassEdit;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\ProductWriter as BaseProductWriter;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product writer
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends BaseProductWriter
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
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
        parent::__construct($mediaManager, $versionManager, $productSaver, $detacher);

        $this->securityContext = $securityContext;
    }


    /**
     * Returns true if user is owner of the product or if the product does not exist yet or fi the token does not exist
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasPermissions(ProductInterface $product)
    {
        if (null === $product->getId() || null === $this->securityContext->getToken()) {
            $isOwner = true;
        } else {
            $isOwner = $this->securityContext->isGranted(Attributes::OWN, $product);
        }

        return $isOwner;
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
