<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\AttributeApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InternalApiRequirementCheckerSpec extends ObjectBehavior
{
    public function let(
        FieldsRequirementChecker $fieldsChecker,
        AttributeApiRequirementChecker $attributeChecker
    ): void {
        $this->beConstructedWith($fieldsChecker, $attributeChecker);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(InternalApiRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_key_properties_is_missing(): void
    {
        $data = [
            'attributes' => []
        ];

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_throw_an_exception_when_key_attributes_is_missing(): void
    {
        $data = [
            'properties' => [],
        ];

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_call_all_checker($fieldsChecker, $attributeChecker): void {
        $data = [
            'properties' => [],
            'attributes' => []
        ];
        $fieldsChecker->check($data['properties'])->shouldBeCalled();
        $attributeChecker->check($data['attributes'])->shouldBeCalled();
        $this->check($data);
    }
}
