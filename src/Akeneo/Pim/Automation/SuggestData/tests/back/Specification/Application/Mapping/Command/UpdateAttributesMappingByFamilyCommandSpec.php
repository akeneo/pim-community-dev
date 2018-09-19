<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(Argument::is('string'), Argument::is('array'));
    }
}
