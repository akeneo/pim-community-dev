<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertToSimpleProductProcessor extends AbstractProcessor
{
    /** @var RemoveParentInterface */
    private $removeParent;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(RemoveParentInterface $removeParent, ValidatorInterface $validator)
    {
        $this->removeParent = $removeParent;
        $this->validator = $validator;
    }

    public function process($product)
    {
        Assert::isInstanceOf($product, ProductInterface::class);

        if (!$product->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                'pim_enrich.mass_edit_action.convert_to_simple_product.error.non_variant_product',
                ['{{ identifier }}' => $product->getIdentifier()],
                new DataInvalidItem($product)
            );

            return null;
        }

        try {
            $this->removeParent->from($product);
        } catch (AccessDeniedException $e) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));

            return null;
        }

        $violations = $this->validator->validate($product);

        if (0 !== $violations->count()) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->addWarningMessage($violations, $product);

            return null;
        }

        return $product;
    }
}
