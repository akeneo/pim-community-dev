<?php

namespace spec\Akeneo\Asset\Component\Factory;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Asset\Component\Factory\ReferenceFactory;
use Akeneo\Asset\Component\Model\ReferenceInterface;

class AssetFactorySpec extends ObjectBehavior
{
    const ASSET_CLASS = 'Akeneo\Asset\Component\Model\Asset';

    function let(ReferenceFactory $referenceFactory, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($referenceFactory, $localeRepository, self::ASSET_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Asset\Component\Factory\AssetFactory');
    }

    function it_creates_a_not_localized_asset($referenceFactory, ReferenceInterface $reference)
    {
        $referenceFactory->create()->willReturn($reference);

        $this->create(false)->shouldReturnAnInstanceOf(self::ASSET_CLASS);
    }

    function it_creates_a_localized_asset(
        $localeRepository,
        $referenceFactory,
        ReferenceInterface $referenceFr,
        ReferenceInterface $referenceEn,
        LocaleInterface $fr_FR,
        LocaleInterface $en_EN
    ) {
        $localeRepository->getActivatedLocales()->willReturn([$fr_FR, $en_EN]);
        $referenceFactory->create($fr_FR)->willReturn($referenceFr);
        $referenceFactory->create($en_EN)->willReturn($referenceEn);

        $this->create(true)->shouldReturnAnInstanceOf(self::ASSET_CLASS);
    }
}
