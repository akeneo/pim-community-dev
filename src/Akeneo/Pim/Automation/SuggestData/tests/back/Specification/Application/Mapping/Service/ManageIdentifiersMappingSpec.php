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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\ManageIdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ManageIdentifiersMappingSpec extends ObjectBehavior
{
    public function let(
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ): void {
        $this->beConstructedWith($identifiersMappingRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ManageIdentifiersMapping::class);
    }
}
