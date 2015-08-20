<?php

namespace spec\PimEnterprise\Component\ProductAsset\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\ReferenceFactory;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

class AssetFactorySpec extends ObjectBehavior
{
    const ASSET_CLASS = 'PimEnterprise\Component\ProductAsset\Model\Asset';

    function let(ReferenceFactory $referenceFactory, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($referenceFactory, $localeRepository, self::ASSET_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Factory\AssetFactory');
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
