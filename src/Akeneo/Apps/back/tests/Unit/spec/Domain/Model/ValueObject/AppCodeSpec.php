<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\ValueObject;

use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppCodeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('magento');
        $this->shouldBeAnInstanceOf(AppCode::class);
    }

    function it_cannot_contains_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(new \InvalidArgumentException('akeneo_apps.app.constraint.code.required'))->duringInstantiation();
    }

    function it_cannot_contains_a_string_longer_than_100_characters()
    {
        $this->beConstructedWith(str_repeat('a', 103));
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_apps.app.constraint.code.too_long')
        )->duringInstantiation();
    }

    function it_contains_only_alphanumeric_characters()
    {
        $this->beConstructedWith('magento-connector');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_apps.app.constraint.code.invalid')
        )->duringInstantiation();
    }

    function it_returns_the_app_code_as_a_string()
    {
        $this->beConstructedWith('magento');
        $this->__toString()->shouldReturn('magento');
    }
}
