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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Service\ManageIdentifiersMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ManageIdentifiersMappingSpec extends ObjectBehavior
{
    function let(
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->beConstructedWith($updateIdentifiersMappingHandler, $identifiersMappingRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ManageIdentifiersMapping::class);
    }

    function it_updates_identifiers_mapping($updateIdentifiersMappingHandler)
    {
        $identifiersMapping = [
            'asin' => 'PIM_asin',
            'brand' => 'PIM_brand',
            'mpn' => 'PIM_mpn',
            'upc' => 'PIM_upc'
        ];

        $updateIdentifiersMappingHandler
            ->handle(new UpdateIdentifiersMappingCommand($identifiersMapping))
            ->shouldBeCalled();

        $this->updateIdentifierMapping($identifiersMapping);
    }
}
