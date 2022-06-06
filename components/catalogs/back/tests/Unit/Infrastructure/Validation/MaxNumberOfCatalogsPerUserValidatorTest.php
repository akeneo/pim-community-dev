<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Domain\Validation\GetOwnerIdInterface;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUser;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUserValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MaxNumberOfCatalogsPerUserValidatorTest extends ConstraintValidatorTestCase
{
    private ?IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery;

    protected function setUp(): void
    {
        $this->isCatalogsNumberLimitReachedQuery = $this->createMock(IsCatalogsNumberLimitReachedQueryInterface::class);

        parent::setUp();
    }

    protected function createValidator()
    {
        return new MaxNumberOfCatalogsPerUserValidator($this->isCatalogsNumberLimitReachedQuery);
    }

    public function testItValidates(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(false);

        $classWithOwnerIdGetter = new class implements GetOwnerIdInterface {
            public function getOwnerId(): int
            {
                return 42;
            }
        };

        $this->validator->validate($classWithOwnerIdGetter, new MaxNumberOfCatalogsPerUser());

        $this->assertNoViolation();
    }

    public function testItDoesNotValidate(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(true);

        $classWithOwnerIdGetter = new class implements GetOwnerIdInterface {
            public function getOwnerId(): int
            {
                return 42;
            }
        };

        $this->validator->validate($classWithOwnerIdGetter, new MaxNumberOfCatalogsPerUser());

        $this->buildViolation('akeneo_catalogs.validation.max_number_of_catalogs_per_user_message')
            ->assertRaised();
    }

    public function testItThrowsAnExceptionIfValueNotImplementsGetOwnerIdInterface(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(false);

        $classWithoutOwnerIdGetter = new class {
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('$value must implements components/catalogs/back/src/Domain/Validation/GetOwnerIdInterface.php');

        $this->validator->validate($classWithoutOwnerIdGetter, new MaxNumberOfCatalogsPerUser());
    }
}
