<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\RegenerateAppSecretCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateAppSecretCommandSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('Magento');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RegenerateAppSecretCommand::class);
    }

    public function it_returns_the_app_code()
    {
        $this->code()->shouldReturn('Magento');
    }
}
