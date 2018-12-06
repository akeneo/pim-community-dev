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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\IdentifiersMappingException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifiersMappingExceptionSpec extends ObjectBehavior
{
    public function it_is_an_identifier_mapping_exception(): void
    {
        $this->beConstructedWith('');

        $this->shouldHaveType(IdentifiersMappingException::class);
        $this->shouldHaveType(\Exception::class);
    }

    public function it_builds_an_exception_if_ask_franklin_server_is_down(): void
    {
        $this->beConstructedThrough('askFranklinServerIsDown', ['A\Fake\Class']);

        $this->getMessage()->shouldReturn('akeneo_suggest_data.entity.identifier_mapping.constraint.ask_franklin_down');
        $this->getClassName()->shouldReturn('A\Fake\Class');
        $this->getMessageParams()->shouldReturn([]);
        $this->getPath()->shouldReturn(null);
    }
}
