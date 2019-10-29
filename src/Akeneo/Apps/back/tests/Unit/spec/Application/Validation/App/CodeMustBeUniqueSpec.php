<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Validation\App;

use Akeneo\Apps\Application\Validation\App\CodeMustBeUnique;
use PhpSpec\ObjectBehavior;

class CodeMustBeUniqueSpec extends ObjectBehavior
{
    public function it_is_a_constraint(): void
    {
        $this->shouldHaveType(CodeMustBeUnique::class);
    }

    public function it_provides_a_target(): void
    {
        $this->getTargets()->shouldReturn(CodeMustBeUnique::PROPERTY_CONSTRAINT);
    }

    public function it_provides_a_tag_to_be_validated(): void
    {
        $this->validatedBy()->shouldReturn('apps_code_must_be_unique');
    }
}
