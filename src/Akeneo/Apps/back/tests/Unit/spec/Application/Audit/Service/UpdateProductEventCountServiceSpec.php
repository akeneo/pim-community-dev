<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Audit\Service;

use Akeneo\Apps\Application\Audit\Service\UpdateProductEventCountService;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountServiceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(UpdateProductEventCountService::class);
    }
}
