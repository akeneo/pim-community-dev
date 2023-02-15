<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Validation;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service\GetCustomAppsNumberLimit;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateCustomAppCommandValidationIntegration extends TestCase
{
    private ?ValidatorInterface $validator;
    private GetCustomAppsNumberLimit $getCustomAppsNumberLimit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->get('validator');
        $this->getCustomAppsNumberLimit = $this->get(GetCustomAppsNumberLimit::class);
        $this->getCustomAppsNumberLimit->setLimit(20);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_validates_the_custom_app(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'New test app',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        ));

        Assert::assertCount(0, $violations);
    }

    public function test_it_invalidates_a_custom_app_with_a_blank_name(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            '',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'name',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.name.not_blank',
        );
    }

    public function test_it_invalidates_a_custom_app_with_a_too_long_name(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'A too long name for a custom app is more than 255 character unless it has been changed in the ' .
            'validation but not in the test. So you should change the test if you change the validation. ' .
            'But, whatever, did you play to Hades? It is a very good game! I need more characters to have 255. ' .
            ' That\'s it, bye!',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'name',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.name.max_length',
        );
    }

    public function test_it_invalidates_a_custom_app_with_a_too_short_name(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'ab',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'name',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.name.min_length',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_activate_url_is_not_an_url(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            'activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'activateUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.activate_url.must_be_url',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_activate_url_is_blank(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            '',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'activateUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.activate_url.not_blank',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_activate_url_is_too_long(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            'http://activate-url-activate-url-activate-url-activate-url-activate-url-activate-url-activate' .
            '-url-activate-url-activate-url-activate-url-activate-url-activate-url-activate-url-activate-url' .
            '-activate-url-activate-url-activate-url-activate-url-activate-url-activate-url-activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'activateUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.activate_url.max_length',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_callback_url_is_too_long(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            'http://activate-url.test',
            'http://callback-url-callback-url-callback-url-callback-url-callback-url-callback-url-callback' .
            '-url-callback-url-callback-url-callback-url-callback-url-callback-url-callback-url-callback-url' .
            '-callback-url-callback-url-callback-url-callback-url-callback-url-callback-url-callback-url-callbac.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'callbackUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.callback_url.max_length',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_callback_url_is_not_an_url(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            'http://activate-url.test',
            'callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'callbackUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.callback_url.must_be_url',
        );
    }

    public function test_it_invalidates_a_custom_app_if_the_callback_url_is_blank(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'Custom app name',
            'http://activate-url.test',
            '',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'callbackUrl',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.callback_url.not_blank',
        );
    }

    public function test_it_invalidates_a_custom_app_with_a_too_long_client_id(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234ClientID1234ClientID1234ClientID1234ClientID1234ClientID1234',
            'Custom app name',
            'http://activate-url.test',
            'callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'clientId',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.client_id.max_length',
        );
    }

    public function test_it_invalidates_a_custom_app_with_a_blank_client_id(): void
    {
        $violations = $this->validator->validate(new CreateCustomAppCommand(
            '',
            'Custom app name',
            'http://activate-url.test',
            'callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            'clientId',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.client_id.not_blank',
        );
    }

    public function test_it_invalidates_a_custom_app_when_limit_is_reached(): void
    {
        $this->getCustomAppsNumberLimit->setLimit(0);

        $violations = $this->validator->validate(new CreateCustomAppCommand(
            'ClientID1234',
            'New test app',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        ));

        $this->assertHasViolation(
            $violations,
            '',
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.limit_reached',
        );
    }

    private function assertHasViolation(
        ConstraintViolationList $constraintViolationList,
        string $propertyPath,
        string $message
    ): void {
        $violationFound = false;
        foreach ($constraintViolationList as $violation) {
            if ($violation->getPropertyPath() === $propertyPath && $violation->getMessage() === $message) {
                $violationFound = true;
                break;
            }
        }

        Assert::assertTrue($violationFound, \sprintf('The violation at property path "%s" has not been found.', $propertyPath));
    }
}
