<?php

namespace spec\Pim\Bundle\EnrichBundle\Provider\StructureVersion;

use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

class StructureVersionProviderSpec extends ObjectBehavior
{
    function let(VersionRepositoryInterface $versionRepository)
    {
        $this->beConstructedWith($versionRepository);
    }

    function it_provides_a_structure_version($versionRepository, Version $lastVersion, \DateTime $lastUpdate)
    {
        $versionRepository->getNewestLogEntryForRessources([])
            ->willReturn($lastVersion);

        $lastVersion->getLoggedAt()->willReturn($lastUpdate);
        $lastUpdate->getTimestamp()->willReturn(12);

        $this->getStructureVersion()->shouldReturn(12);
    }

    function it_provides_a_structure_version_for_given_resources($versionRepository, Version $lastVersion, \DateTime $lastUpdate)
    {
        $this->addResource('Locale');
        $versionRepository->getNewestLogEntryForRessources(['Locale'])
            ->willReturn($lastVersion);

        $lastVersion->getLoggedAt()->willReturn($lastUpdate);
        $lastUpdate->getTimestamp()->willReturn(12);

        $this->getStructureVersion()->shouldReturn(12);
    }

    function it_provides_null_when_no_history_is_available($versionRepository)
    {
        $this->addResource('Locale');

        $versionRepository->getNewestLogEntryForRessources(['Locale'])
            ->willReturn(null);

        $this->getStructureVersion()->shouldReturn(null);
    }
}
