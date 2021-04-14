<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionsValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class ReplaceMultiSelectOperationHandler implements OperationHandlerInterface
{

    private OptionsValueFactory $optionsValueFactory;

    public function __construct(OptionsValueFactory $optionsValueFactory)
    {
        $this->optionsValueFactory = $optionsValueFactory;
    }

    public function supports(array $operation, Attribute $attribute, $value): bool
    {
        return $operation['type'] === 'replace' && $attribute->type() === AttributeTypes::OPTION_MULTI_SELECT && $value instanceof ValueInterface;
    }

    /** @param ValueInterface $value */
    public function handleOperation(array $operation, Attribute $attribute, $value)
    {
        $results = [];

        foreach ($value->getData() as $data) {
            $results[] = $operation['mapping'][$data] ?? $data;
        }

        return $this->optionsValueFactory->createWithoutCheckingData(
            $attribute,
            $value->getScopeCode(),
            $value->getLocaleCode(),
            $results
        );
    }
}
