<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserId
{
    /** @var int */
    private $id;

    public function __construct(int $id)
    {
        if (0 > $id) {
            throw new \InvalidArgumentException('User id must be positive.');
        }

        $this->id = $id;
    }

    public function id(): int
    {
        return $this->id;
    }
}
