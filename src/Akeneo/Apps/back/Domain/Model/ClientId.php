<?php
declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientId
{
    /** @var int */
    private $id;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function create(int $id): self
    {
        if (0 > $id) {
            throw new \InvalidArgumentException('Client id must be positive.');
        }

        return new self($id);
    }

    public function id(): int
    {
        return $this->id;
    }
}
