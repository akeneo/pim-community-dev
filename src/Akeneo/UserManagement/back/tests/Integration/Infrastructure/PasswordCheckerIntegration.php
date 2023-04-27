<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\UserManagement\Integration\Infrastructure;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\PasswordCheckerInterface;
use PHPUnit\Framework\Assert;

final class PasswordCheckerIntegration extends TestCase
{

    public function testItValidatePasswordLength(): void
    {
        $violations = $this->getPasswordChecker()->validatePasswordLength('12345678', 'password');
        Assert::assertEmpty($violations);
    }

    public function testPasswordLengthTooShort(): void
    {
        $violations = $this->getPasswordChecker()->validatePasswordLength('1234', 'password');
        Assert::assertCount(1, $violations);
        $violation = $violations->get(0);
        Assert::assertEquals('Password must contain at least 8 characters', $violation->getMessage());
    }

    public function testPasswordLengthTooLong(): void
    {
        $password = <<<EOF
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus orci enim, dignissim a elementum at, ullamcorper nec velit. Vestibulum vestibulum enim vitae justo sollicitudin, sit amet tincidunt massa tincidunt. Aenean in varius felis. Cras quam ex, pretium sed sapien nec, ultricies bibendum neque. Phasellus fermentum, est congue faucibus dignissim, purus nulla porttitor nulla, eget aliquet massa diam a purus. Donec faucibus eleifend nunc eget convallis. Fusce nec mi vitae nisl ultricies laoreet in a nunc. Ut elementum faucibus mollis. Quisque mollis, lacus vel porta facilisis, velit erat volutpat lorem, in ultricies lacus felis et sem. In libero magna, elementum vel ipsum ac, convallis accumsan tortor. Proin venenatis enim nisl, vel porta ligula ullamcorper non. Vestibulum id euismod neque. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc tempus accumsan accumsan.
            Aliquam ut sem sed elit consectetur pulvinar. Proin consequat gravida hendrerit. Duis aliquam purus quis orci consectetur, vel tristique massa finibus. Cras vitae auctor orci, sit amet mollis augue. Phasellus eu tristique justo. Nullam condimentum malesuada leo. Vivamus quis leo ac lorem dapibus mollis. Donec at pellentesque nisi, nec dictum ex. Morbi sit amet sollicitudin mauris. Phasellus non quam leo. Aenean facilisis sem dolor. Nunc ornare tortor nec efficitur elementum. Curabitur bibendum quam arcu, facilisis ultricies arcu scelerisque quis. Vestibulum ullamcorper nibh non odio maximus feugiat.
            Nunc felis leo, posuere non nisl id, sagittis scelerisque purus. Curabitur suscipit in mi eget efficitur. Nulla consectetur sem sem, vel scelerisque justo tristique eget. Duis luctus condimentum ipsum, rutrum ullamcorper quam bibendum at. Vestibulum auctor pharetra libero, congue scelerisque nunc cursus et. Curabitur elementum cursus diam ac condimentum. Mauris consequat hendrerit efficitur. Nullam aliquet convallis aliquet. Etiam at nulla urna. Vestibulum cursus lacinia metus, a posuere quam dapibus in. Nam varius, nulla et laoreet tristique, eros lectus interdum tortor, eu pellentesque felis dui ut neque.
            Donec mattis leo mauris, a sagittis lorem ullamcorper et. Curabitur tristique lectus quis pellentesque convallis. Donec viverra, purus quis auctor molestie, enim mauris hendrerit nisl, eu volutpat neque mauris sed massa. Proin vestibulum, arcu facilisis interdum placerat, quam quam consectetur ipsum, id vestibulum justo turpis non lorem. Phasellus nec venenatis urna. Vestibulum tincidunt sollicitudin odio eu gravida. Maecenas luctus imperdiet sem at malesuada. In dapibus, purus vitae vehicula dapibus, sapien libero volutpat nisl, eu rhoncus libero felis sit amet erat. Sed aliquet faucibus placerat. Proin venenatis eu risus et feugiat. Etiam nec velit egestas, facilisis erat vel, pretium nunc. Fusce hendrerit commodo nisi, vitae elementum nulla ornare id. Integer eu sapien id enim ullamcorper rutrum ac eu quam. Nullam diam lacus, sodales eu dolor ut, sodales dignissim neque. Donec sagittis turpis at felis varius faucibus.
            Nullam non leo eu leo aliquet porta. Phasellus ultrices pharetra arcu, in auctor elit vehicula at. Nam auctor odio eget erat dignissim, at egestas dui sodales. Donec sapien diam, tincidunt ac urna vel, imperdiet auctor quam. Mauris et enim ac magna vestibulum congue sollicitudin pellentesque risus. Aenean a nunc at felis commodo hendrerit non vitae massa. Nulla a nunc at tortor luctus tristique vel vel urna. Sed auctor porta odio a ullamcorper. Vivamus tempus magna dui. Vivamus a tristique turpis, ac commodo metus. Morbi id justo euismod purus feugiat accumsan eu vel sem. Praesent dui dui, ornare eu tortor non, lobortis semper nulla. Sed blandit est ultrices luctus tincidunt. Aliquam rutrum aliquam nisl vel feugiat. Aenean dignissim risus id risus ornare, eu gravida lectus malesuada.
            Maecenas interdum, neque blandit ultricies vulputate, sapien orci rutrum dui, id malesuada lacus erat ac massa. In hac habitasse platea dictumst. Ut gravida turpis venenatis ante lobortis fringilla. Cras efficitur justo.
        EOF;

        $violations = $this->getPasswordChecker()->validatePasswordLength($password, 'password');
        Assert::assertCount(1, $violations);
        $violation = $violations->get(0);
        Assert::assertEquals('Password must contain less than 4096 characters', $violation->getMessage());
    }

    public function testItValidatePasswordsMatch(): void
    {
        $violations = $this->getPasswordChecker()->validatePasswordMatch('password', 'password', 'password');
        Assert::assertEmpty($violations);
    }

    public function testPasswordsDoesNotMatch(): void
    {
        $violations = $this->getPasswordChecker()->validatePasswordMatch('password', 'password1', 'password');
        Assert::assertCount(1, $violations);
        $violation = $violations->get(0);
        Assert::assertEquals('Passwords do not match', $violation->getMessage());
    }

    public function testItValidateUserPassword(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);
        $data = [
            'current_password' => 'userA',
            'new_password' => 'realPassword',
            'new_password_repeat' => 'realPassword',
        ];
        $violations = $this->getPasswordChecker()->validatePassword($user, $data);
        Assert::assertEmpty($violations);
    }

    public function testUserWrongCurrentPassword(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);
        $data = [
            'current_password' => 'userAFake',
            'new_password' => '1234',
            'new_password_repeat' => '12345',
        ];
        $violations = $this->getPasswordChecker()->validatePassword($user, $data);
        Assert::assertCount(3, $violations);
        $violation = $violations->get(0);
        Assert::assertEquals('Wrong password', $violation->getMessage());
        $violation = $violations->get(1);
        Assert::assertEquals('Password must contain at least 8 characters', $violation->getMessage());
        $violation = $violations->get(2);
        Assert::assertEquals('Passwords do not match', $violation->getMessage());
    }

    private function getPasswordChecker(): PasswordCheckerInterface {
        return $this->get(PasswordCheckerInterface::class);
    }

    private function getUserLoader(): UserLoader {
        return $this->get(UserLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
