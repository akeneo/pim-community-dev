<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Service;

use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CreateUserIntegration extends TestCase
{
    /** @var CreateUserInterface */
    private $createUser;

    public function test_that_it_creates_a_user()
    {
        $userId = $this->createUser->execute(
            'magento',
            'Magento Connector',
            'APP',
            'magento',
            'admin@anemail.com'
        );

        Assert::assertInstanceOf(UserId::class, $userId);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUser = $this->get('akeneo_app.service.user.create_user');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
