<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection as DTOValueCollection;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product\Product;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Pim\Component\Catalog\Model\ValueCollection;
use Webmozart\Assert\Assert;

/**
 * Class EditProductCommandHandler
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditProductCommandHandler
{
    public function __invoke(EditProductCommand $command): void
    {
        $violations = $this->get('validator')->validate($command);
        Assert::assertCount(0, $violations);

        $product = $this->productRepository->find($command->identifier());
        if (null === $product) {
            $product = new Product($command->identifier());
        }

        // how to handle it with permissions? dedicated service to have the diff?
        $product->categorize($command->categories());

        // should be a factory
        $valueCollection = $this->createValueCollection($command->values());
        $product->addValues($valueCollection);

        $this->productRepository->add($product);
    }

    private function createValueCollection(DTOValueCollection $dtoValues): ValueCollection
    {
        $values = [];
        foreach ($dtoValues as $dtoValue) {
            $attribute = $this->attributeRepository->find($dtoValue->attributeCode());
            $values = new ScalarValue($attribute, $dtoValue->localeCode(), $dtoValue->channelCode(), $dtoValue->data());
        }

        return new ValueCollection($values);
    }
}
