<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Channel\tests\Integration\Infrastructure\Query;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Test\Channel\Integration\ChannelTestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

final class LRUCachedIsLocaleEditableTest extends ChannelTestCase
{
    private IsLocaleEditable $isLocaleEditable;
    private UserInterface $userWithPermission;
    private UserInterface $userWithoutPermission;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->isLocaleEditable = $this->get(IsLocaleEditable::class);
        $this->loadChannelFunctionalFixtures();

        $this->userWithPermission = $this->createUser('user_with_locale_permission', ['ROLE_USER'], ['Redactor']);
        $this->userWithoutPermission = $this->createUser('user_without_locale_permission', ['ROLE_USER'], ['All']);
    }

    /** @test */
    public function itReturnsAllActivatedLocaleCodesEditableByUser(): void
    {
        Assert::assertTrue($this->isLocaleEditable->forUserId('en_US', $this->userWithPermission->getId()));
        Assert::assertTrue($this->isLocaleEditable->forUserId('fr_FR', $this->userWithPermission->getId()));
        Assert::assertFalse($this->isLocaleEditable->forUserId('en_US', $this->userWithoutPermission->getId()));
        Assert::assertFalse($this->isLocaleEditable->forUserId('fr_FR', $this->userWithoutPermission->getId()));
        Assert::assertFalse($this->isLocaleEditable->forUserId('en_US', -10));
        Assert::assertFalse($this->isLocaleEditable->forUserId('fr_FR', -10));
    }
}
