<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\ValueObject;

use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLabelSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('Magento Connector');
        $this->shouldBeAnInstanceOf(AppLabel::class);
    }

    public function it_cannot_contains_a_string_longer_than_100_characters()
    {
        $this->beConstructedWith(str_repeat('a', 101));
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_apps.app.constraint.label.too_long')
        )->duringInstantiation();
    }

    public function it_returns_the_app_label_as_a_string()
    {
        $this->beConstructedWith('Magento Connector');
        $this->__toString()->shouldReturn('Magento Connector');
    }

    public function it_cannot_contains_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_apps.app.constraint.label.required')
        )->duringInstantiation();
    }
}
