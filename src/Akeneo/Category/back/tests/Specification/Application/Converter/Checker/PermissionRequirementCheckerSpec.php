<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use AkeneoEnterprise\Category\Application\Converter\Checker\PermissionRequirementChecker;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PermissionRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PermissionRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_key_is_incorrect(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'wrong_permission' => [1, 2],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_is_incorrect(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'view' => "",
                ]
            );

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'view' => 1,
                ]
            );

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'view' => false,
                ]
            );
    }
}
