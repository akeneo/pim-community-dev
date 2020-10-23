<?php
declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorSpec extends ObjectBehavior
{
    public function it_is_initializable($author): void
    {
        $this->shouldHaveType(Author::class);
    }

    public function it_does_create_an_author_from_user(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        Assert::assertEquals('julia', $author->name());
        Assert::assertEquals(Author::TYPE_UI, $author->type());
    }

    public function it_does_create_an_api_author_from_user(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(true);
        $author = Author::fromUser($user->getWrappedObject());

        Assert::assertEquals('julia', $author->name());
        Assert::assertEquals(Author::TYPE_API, $author->type());
    }
}
