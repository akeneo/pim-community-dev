<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskSpec extends ObjectBehavior
{
    function it_can_be_enabled()
    {
        $this->beConstructedWith('task-code', 'bin/console list', '* * * * *', false);
        $this->isEnabled()->shouldBe(false);
        $this->enable();
        $this->isEnabled()->shouldBe(true);
    }

    function it_can_be_disabled()
    {
        $this->beConstructedWith('task-code', 'bin/console list', '* * * * *', true);
        $this->isEnabled()->shouldBe(true);
        $this->disable();
        $this->isEnabled()->shouldBe(false);
    }
}
