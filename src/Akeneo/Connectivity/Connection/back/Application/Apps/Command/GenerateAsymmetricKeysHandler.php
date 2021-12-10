<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAsymmetricKeysHandler
{
    public function __construct(private AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator)
    {
    }

    public function handle(GenerateAsymmetricKeysCommand $command): void
    {
        $keys = $this->asymmetricKeysGenerator->generate();

        //TODO:: save into database
    }
}
