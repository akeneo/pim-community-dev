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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;

class InMemoryFindConnectorAttributeOptions implements FindConnectorAttributeOptionsInterface
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
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): ?array
    {
        $connectorAttribute = $this->attributes[(string) $referenceEntityIdentifier][(string) $attributeCode] ?? null;

        if (null === $connectorAttribute) {
            return null;
        }

        $options = $connectorAttribute->normalize()['options'];


        if (empty($options)) {
            return null;
        }

        $connectorOptions = [];

        foreach ($options as $option) {
            $connectorOptions[] = new ConnectorAttributeOption(
                OptionCode::fromString($option['code']),
                LabelCollection::fromArray($option['labels'])
            );
        }

        return $connectorOptions;
    }
}
