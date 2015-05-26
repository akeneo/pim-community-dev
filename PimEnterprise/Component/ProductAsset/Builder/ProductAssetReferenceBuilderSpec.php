<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;
use Prophecy\Argument;

class ProductAssetReferenceBuilderSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, LocaleInterface $en_US, LocaleInterface $fr_FR)
    {
        $localeRepository->getActivatedLocales()->willReturn([$en_US, $fr_FR]);

        $this->beConstructedWith($localeRepository);
    }

    function it_builds_a_localized_reference($en_US, ProductAssetInterface $asset)
    {
        $reference = $this->buildOne($asset, $en_US);

        $reference->getAsset()->shouldBe($asset);
        $reference->getLocale()->shouldBe($en_US);
    }

    function it_builds_a_non_localized_reference(ProductAssetInterface $asset)
    {
        $reference = $this->buildOne($asset);

        $reference->getAsset()->shouldBe($asset);
        $reference->getLocale()->shouldBe(null);
    }

    function it_builds_all_localized_references(ProductAssetInterface $asset)
    {
        $all = $this->buildAllLocalized($asset);
        $all->shouldHaveCount(2);
        $all->shouldBeArrayOfReferences();
    }

    function it_builds_missing_localized_references($en_US, $fr_FR, ProductAssetInterface $asset)
    {
        $asset->hasReference($en_US)->willReturn(true);
        $asset->hasReference($fr_FR)->willReturn(false);
        $asset->addReference(Argument::any())->shouldBeCalledTimes(1);

        $all = $this->buildMissingLocalized($asset);
        $all->shouldHaveCount(1);
        $all->shouldBeArrayOfReferences();

    }

    public function getMatchers()
    {
        return [
            'beArrayOfReferences' => function ($subject) {
                foreach ($subject as $row) {
                    if (!$row instanceof ProductAssetReferenceInterface) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
