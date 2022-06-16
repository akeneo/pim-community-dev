<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Application\Validation;

use Akeneo\Catalogs\Application\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUser;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUserValidator;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
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

        $command = new CreateCatalogCommand(
            id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
            name: 'foo',
            ownerId: 42,
        );

        $this->validator->validate($command, new MaxNumberOfCatalogsPerUser());

        $this->assertNoViolation();
    }

    public function testItDoesNotValidate(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(true);

        $command = new CreateCatalogCommand(
            id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
            name: 'foo',
            ownerId: 42,
        );

        $this->validator->validate($command, new MaxNumberOfCatalogsPerUser());

        $this->buildViolation('akeneo_catalogs.validation.max_number_of_catalogs_per_user_message')
            ->assertRaised();
    }

    public function testItThrowsAnExceptionIfValueNotCreateCatalogCommand(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(false);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf(
            'MaxNumberOfCatalogsPerUserValidator can only be used on instances of "%s"',
            CreateCatalogCommand::class,
        ));

        $this->validator->validate(new \stdClass(), new MaxNumberOfCatalogsPerUser());
    }
}
