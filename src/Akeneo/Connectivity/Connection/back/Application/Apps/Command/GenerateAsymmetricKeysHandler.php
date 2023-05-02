<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveAsymmetricKeysQueryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAsymmetricKeysHandler
{
    public function __construct(private AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator, private SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery)
    {
    }

    public function handle(GenerateAsymmetricKeysCommand $command): void
    {
        $keys = $this->asymmetricKeysGenerator->generate();
        $this->saveAsymmetricKeysQuery->execute($keys);
    }
}
