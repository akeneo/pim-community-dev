<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\FileInfo\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoesImageExistQuery implements DoesImageExistQueryInterface
{
    private $database = [
        'a/b/c/image.jpg',
        'c/b/a/image.jpg'
    ];

    public function execute(string $filePath): bool
    {
        return in_array($filePath, $this->database);
    }
}
