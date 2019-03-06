<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\EventDispatcher\Event;

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

    public function __construct(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeIdentifier $attributeIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->attributeIdentifier = $attributeIdentifier;
    }
}
