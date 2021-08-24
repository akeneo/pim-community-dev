<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamilySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(NotEmptyFamily::class);
    }

    function it_is_a_validator_constraint(): void
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('The family cannot be "null" because your product with the %sku% identifier is a variant product.');
    }

    function it_has_a_property_path()
    {
        $this->propertyPath->shouldBe('family');
    }

    function it_has_a_validated_by(): void
    {
        $this->validatedBy()->shouldBe('pim_family_not_empty');
    }

    function it_has_targets(): void
    {
        $this->getTargets()->shouldBe(NotEmptyFamily::CLASS_CONSTRAINT);
    }
}
