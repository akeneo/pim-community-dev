<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\AddParent;

/**
 * Processor to add products to an existing product model
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToExistingProductModelProcessor extends AbstractProcessor
{
    /** @var AddParent */
    private $addParent;

    /**
     * @param AddParent $addParent
     */
    public function __construct(AddParent $addParent)
    {
        $this->addParent = $addParent;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $parentProductModelCode = $actions[0]['value'];

        if ($product instanceof VariantProductInterface) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        try {
            $product = $this->addParent->to($product, $parentProductModelCode);
        } catch (\InvalidArgumentException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));

            return null;
        }

        return $product;
    }
}
