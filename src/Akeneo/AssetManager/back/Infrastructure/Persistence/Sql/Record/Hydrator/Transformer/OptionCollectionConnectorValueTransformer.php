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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionConnectorValueTransformer implements ConnectorValueTransformerInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof OptionCollectionAttribute;
    }

    public function transform(array $normalizedValue, AbstractAttribute $attribute): ?array
    {
        Assert::true($this->supports($attribute));

        $existingOptions = $this->filterExistingOptions($normalizedValue['data'], $attribute);

        if (empty($existingOptions)) {
            return null;
        }

        return [
            'locale'  => $normalizedValue['locale'],
            'channel' => $normalizedValue['channel'],
            'data'    => $existingOptions,
        ];
    }

    private function filterExistingOptions(array $normalizedOptionCodes, OptionCollectionAttribute $attribute): array
    {
        return array_values(array_filter($normalizedOptionCodes, function ($normalizedOptionCode) use ($attribute) {
            return $attribute->hasAttributeOption(OptionCode::fromString($normalizedOptionCode));
        }));
    }
}
