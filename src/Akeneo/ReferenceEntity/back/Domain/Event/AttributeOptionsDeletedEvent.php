<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsDeletedEvent
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
