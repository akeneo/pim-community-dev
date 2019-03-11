<?php

namespace Specification\Akeneo\Asset\Component\Builder;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Prophecy\Argument;

class ReferenceBuilderSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, LocaleInterface $en_US, LocaleInterface $fr_FR)
    {
        $localeRepository->getActivatedLocales()->willReturn([$en_US, $fr_FR]);

        $this->beConstructedWith($localeRepository);
    }

    function it_builds_a_localized_reference($en_US, AssetInterface $asset)
    {
        $reference = $this->buildOne($asset, $en_US);

        $reference->getAsset()->shouldBe($asset);
        $reference->getLocale()->shouldBe($en_US);
    }

    function it_builds_a_non_localized_reference(AssetInterface $asset)
    {
        $reference = $this->buildOne($asset);

        $reference->getAsset()->shouldBe($asset);
        $reference->getLocale()->shouldBe(null);
    }

    function it_builds_all_localized_references(AssetInterface $asset)
    {
        $all = $this->buildAllLocalized($asset);
        $all->shouldHaveCount(2);
        $all->shouldBeArrayOfReferences();
    }

    function it_builds_missing_localized_references($en_US, $fr_FR, AssetInterface $asset)
    {
        $asset->isEmpty($en_US)->willReturn(true);
        $asset->isEmpty($fr_FR)->willReturn(false);
        $asset->addReference(Argument::any())->shouldBeCalledTimes(1);

        $all = $this->buildMissingLocalized($asset);
        $all->shouldHaveCount(1);
        $all->shouldBeArrayOfReferences();

    }

    public function getMatchers(): array
    {
        return [
            'beArrayOfReferences' => function ($subject) {
                foreach ($subject as $row) {
                    if (!$row instanceof ReferenceInterface) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
