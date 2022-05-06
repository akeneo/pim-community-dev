<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveAsymmetricKeysQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAsymmetricKeysHandlerSpec extends ObjectBehavior
{
    public function let(
        AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator,
        SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery
    ): void {
        $this->beConstructedWith($asymmetricKeysGenerator, $saveAsymmetricKeysQuery);
    }

    public function it_is_instantiable(): void
    {
        $this->beAnInstanceOf(GenerateAsymmetricKeysHandler::class);
    }

    public function it_generates_asymemtric_keys(
        AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator,
        SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery
    ): void {
        $keys = AsymmetricKeys::create('a_public_key', 'a_private_key');
        $asymmetricKeysGenerator->generate()->willReturn($keys);

        $saveAsymmetricKeysQuery->execute($keys)->shouldBeCalled();

        $this->handle(new GenerateAsymmetricKeysCommand());
    }
}
