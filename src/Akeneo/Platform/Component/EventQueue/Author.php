<?php
declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Author
{
    /** @var string */
    const TYPE_API = 'api';

    /** @var string */
    const TYPE_UI = 'ui';

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    private function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function fromUser(UserInterface $user): Author
    {
        $type = $user->isApiUser() ? self::TYPE_API : self::TYPE_UI;

        return new self($user->getUsername(), $type);
    }

    public static function fromNameAndType(string $name, string $type): Author
    {
        Assert::oneOf($type, [self::TYPE_API, self::TYPE_UI]);

        return new self($name, $type);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }
}
