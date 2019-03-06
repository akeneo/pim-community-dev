<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\EventDispatcher\Event;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class AttributeOptionsDeletedEvent extends Event
{
    /** @var ReferenceEntityIdentifier  */
    private $referenceEntityIdentifier;

    /** @var AttributeIdentifier  */
    private $attributeIdentifier;

    /** @var AttributeOption[] */
    private $deletedAttributeOptions;

    public function __construct(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeIdentifier $attributeIdentifier,
        array $deletedAttributeOptions
    ) {
        Assert::allIsInstanceOf($deletedAttributeOptions, AttributeOption::class);
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->attributeIdentifier = $attributeIdentifier;
        $this->deletedAttributeOptions = $deletedAttributeOptions;
    }

    public function getAttributeIdentifier(): AttributeIdentifier
    {
        return $this->attributeIdentifier;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    /**
     * @return AttributeOption[]
     */
    public function getDeletedAttributeOptions(): array
    {
       return $this->deletedAttributeOptions;
    }
}
