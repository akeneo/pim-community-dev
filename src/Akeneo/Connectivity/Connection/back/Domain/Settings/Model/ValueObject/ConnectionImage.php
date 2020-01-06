<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionImage
{
    private const CONSTRAINT_KEY = 'akeneo_connectivity.connection.connection.constraint.image.%s';
    private $filePath;

    public function __construct(string $filePath)
    {
        if (empty($filePath)) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'not_empty'));
        }

        $this->filePath = $filePath;
    }

    public function __toString(): string
    {
        return $this->filePath;
    }
}
