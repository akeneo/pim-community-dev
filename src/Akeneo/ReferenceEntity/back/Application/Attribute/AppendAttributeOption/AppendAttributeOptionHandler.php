<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Attribute\AppendAttributeOption;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

class AppendAttributeOptionHandler
{
    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(AppendAttributeOptionCommand $command): void
    {
        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier),
            AttributeCode::fromString($command->attributeCode)
        );

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        Assert::isInstanceOfAny($attribute, [OptionCollectionAttribute::class, OptionAttribute::class]);
        Assert::false($attribute->hasAttributeOption(OptionCode::fromString($command->optionCode)));

        $option = AttributeOption::create(
            OptionCode::fromString($command->optionCode),
            LabelCollection::fromArray($command->labels)
        );

        $attribute->addOption($option);
        $this->attributeRepository->update($attribute);
    }
}
