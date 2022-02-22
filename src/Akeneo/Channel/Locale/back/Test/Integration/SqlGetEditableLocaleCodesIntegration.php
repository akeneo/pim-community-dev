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

namespace AkeneoEnterprise\Test\Channel\Locale\Integration;

use Akeneo\Channel\Locale\API\Query\GetEditableLocaleCodes;
use Akeneo\Test\Channel\Locale\Integration\ChannelTestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

final class SqlGetEditableLocaleCodesIntegration extends ChannelTestCase
{
    private UserInterface $userWithPermission;
    private UserInterface $userWithoutPermission;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlGetEditableLocaleCodes = $this->get(GetEditableLocaleCodes::class);
        $this->loadChannelFunctionalFixtures();

        $this->userWithPermission = $this->createUser('user_with_locale_permission', ['ROLE_USER'], ['Redactor']);
        $this->userWithoutPermission = $this->createUser('user_without_locale_permission', ['ROLE_USER'], ['All']);
    }

    /** @test */
    public function it_returns_all_activated_locale_codes_editable_by_user(): void
    {
        Assert::assertEqualsCanonicalizing(
            ['en_US', 'fr_FR'],
            $this->sqlGetEditableLocaleCodes->forUserId($this->userWithPermission->getId())
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->sqlGetEditableLocaleCodes->forUserId($this->userWithoutPermission->getId())
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->sqlGetEditableLocaleCodes->forUserId(-10)
        );
    }
}
