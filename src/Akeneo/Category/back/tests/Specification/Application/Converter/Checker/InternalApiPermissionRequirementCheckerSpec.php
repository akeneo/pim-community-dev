<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\AttributeApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use AkeneoEnterprise\Category\Application\Converter\Checker\InternalApiPermissionRequirementChecker;
use AkeneoEnterprise\Category\Application\Converter\Checker\PermissionRequirementChecker;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InternalApiPermissionRequirementCheckerSpec extends ObjectBehavior
{
    public function let(
        FieldsRequirementChecker $fieldsChecker,
        AttributeApiRequirementChecker $attributeChecker,
        PermissionRequirementChecker $permissionRequirementChecker
    ): void {
        $this->beConstructedWith($fieldsChecker, $attributeChecker, $permissionRequirementChecker);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(InternalApiPermissionRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_key_permissions_is_missing(): void
    {
        $data = [
            'properties' => [],
            'attributes' => []
        ];

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_call_all_checker($fieldsChecker, $attributeChecker, $permissionRequirementChecker): void {
        $data = [
            'properties' => [],
            'attributes' => [],
            'permissions' => []
        ];
        $fieldsChecker->check($data['properties'])->shouldBeCalled();
        $attributeChecker->check($data['attributes'])->shouldBeCalled();
        $permissionRequirementChecker->check($data['permissions'])->shouldBeCalled();
        $this->check($data);
    }
}
