<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class CreateGroupPageIntegration extends ControllerIntegrationTestCase
{
    public function test_it_renders_the_create_group_page_without_error(): void
    {
        $this->logIn('admin');

        $response = $this->callRoute('pim_user_group_create');

        $this->assertStatusCode($response, Response::HTTP_OK);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
