<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\User;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUser;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserIntegration extends TestCase
{
    private ?CreateUser $createUser;
    private ?UserRepositoryInterface $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->createUser = $this->get(CreateUser::class);
        $this->userRepository = $this->get('pim_user.repository.user');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_a_user(): void
    {
        $userId = $this->createUser->execute('x57L54a93CXq', 'magento', ['Manager'], ['ROLE_USER']);

        /** @var UserInterface|null $user */
        $user = $this->userRepository->find($userId);

        Assert::assertNotNull($user);
        Assert::assertSame('x57L54a93CXq', $user->getUserIdentifier());
        Assert::assertSame('magento', $user->getFullName());
        Assert::assertTrue($user->isApiUser());
        Assert::assertSame(['Manager'], $user->getGroupNames());
        Assert::assertSame(['ROLE_USER'], $user->getRoles());
    }
}
