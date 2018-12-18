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

namespace Akeneo\ReferenceEntity\Common\Fake\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;

class InMemoryFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
{
    /** @var ConnectorAttribute[] */
    private $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        ConnectorAttribute $connectorAttribute
    ): void {
        $this->attributes[(string) $referenceEntityIdentifier][(string) $attributeCode] = $connectorAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
    {
        // Return the matching attribute option instead
        $connectorAttribute = $this->attributes[(string) $referenceEntityIdentifier][(string) $attributeCode] ?? null;

        if (null === $connectorAttribute)
        {
            return null;
        }
        
        $options = $connectorAttribute->normalize()['options'];

        var_dump($options);

        $matchingOption = current(array_filter($options, function ($option) use ($optionCode) {
            return $option['code'] === $optionCode;
        }));


        if (!$matchingOption) {
            return null;
        }

        $connectorOption = new ConnectorAttributeOption(
            OptionCode::fromString($matchingOption['code']),
            LabelCollection::fromArray($matchingOption['labels'])
        );

        return $connectorOption;
    }
}
