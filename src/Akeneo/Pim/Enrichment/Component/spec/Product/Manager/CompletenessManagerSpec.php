<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Manager;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class CompletenessManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CompletenessGeneratorInterface $generator,
        CompletenessRemoverInterface $remover,
        ValueCompleteCheckerInterface $valueCompleteChecker
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $channelRepository,
            $localeRepository,
            $generator,
            $remover,
            $valueCompleteChecker
        );
    }
}
