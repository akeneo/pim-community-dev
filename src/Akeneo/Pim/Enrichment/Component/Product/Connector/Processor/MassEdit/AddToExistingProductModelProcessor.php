<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param AddParent          $addParent
     * @param ValidatorInterface $validator
     */
    public function __construct(AddParent $addParent, ValidatorInterface $validator)
    {
        $this->addParent = $addParent;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $parentProductModelCode = $actions[0]['value'];

        if ($product->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                'The parent of a variant product cannot be changed',
                [],
                new DataInvalidItem($product)
            );

            return null;
        }

        try {
            $product = $this->addParent->to($product, $parentProductModelCode);
        } catch (\InvalidArgumentException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));

            return null;
        }

        if (!$this->isProductValid($product)) {
            return null;
        }

        return $product;
    }


    /**
     * Validate the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    private function isProductValid(ProductInterface $product): bool
    {
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }
}
