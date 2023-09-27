<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionBuilderSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, VersionFactory $versionFactory)
    {
        $this->beConstructedWith($normalizer, $versionFactory);
    }

    function it_builds_versions_for_versionable_entities($normalizer, $versionFactory, ProductInterface $product, Version $version)
    {
        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->getUuid()->willReturn($uuid);
        $normalizer->normalize($product, 'flat', [])->willReturn(['bar' => 'baz']);
        $versionFactory->create(Argument::Any(), null, $uuid, 'foo', null)->willReturn($version);
        $version->setVersion(1)->willReturn($version);
        $version->setSnapshot(['bar' => 'baz'])->willReturn($version);
        $version->setChangeset(['bar' => ['old' => '', 'new' => 'baz']])->willReturn($version);
        $this->buildVersion($product, 'foo');
    }

    function it_creates_pending_version($versionFactory, ProductInterface $product, Version $pending)
    {
        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->getUuid()->willReturn($uuid);
        $versionFactory->create(Argument::Any(), null, $uuid, 'baz', null)->willReturn($pending);
        $pending->getChangeset()->willReturn($pending);
        $pending->setChangeset([])->willReturn($pending);
        $pending->getAuthor()->willReturn('baz');
        $pending->isPending()->willReturn(true);

        $version = $this->createPendingVersion($product, 'baz', []);
        $version->shouldBeAnInstanceOf(Version::class);
        $version->getAuthor()->shouldReturn('baz');
        $version->isPending()->shouldReturn(true);
    }

    function it_builds_pending_versions(Version $pending)
    {
        $pending->setVersion(1)->willReturn($pending);
        $pending->setSnapshot(['foo' => 'bar'])->willReturn($pending);
        $pending->getChangeset()->willReturn(['foo' => 'bar']);

        $pending->setChangeset(['foo' => ['old' => '', 'new' => 'bar']])->shouldBeCalled()->willReturn($pending);

        $this->buildPendingVersion($pending);
    }

    function it_builds_pending_versions_with_attribute_with_numeric_code(Version $pending)
    {
        $pending->setVersion(1)->willReturn($pending);
        $pending->setSnapshot([12345678 => 'bar'])->willReturn($pending);
        $pending->getChangeset()->willReturn([12345678 => 'bar']);

        $pending->setChangeset([12345678 => ['old' => '', 'new' => 'bar']])->shouldBeCalled()->willReturn($pending);

        $this->buildPendingVersion($pending);
    }

    function it_compare_versions(Version $pending, Version $previousPending)
    {
        $previousPending->getVersion()->willReturn(1);
        $previousPending->getSnapshot()->willReturn(['test' => '00112233']);

        $pending->setVersion(2)->willReturn($pending);
        $pending->setSnapshot(['test' => '0112233'])->willReturn($pending);
        $pending->getChangeset()->willReturn(['test' => '0112233']);

        $pending->setChangeset(['test' => ['old' => '00112233', 'new' => '0112233']])->willReturn($pending);

        $this->buildPendingVersion($pending, $previousPending);
    }

    /**
     * @see https://akeneo.atlassian.net/browse/PIM-9152
     */
    function it_builds_versions_and_handle_correctly_the_old_versioning_date_format(
        $normalizer,
        $versionFactory,
        ProductModelInterface $productModel,
        Version $previousVersion,
        Version $version
    ) {
        $normalizer->normalize($productModel, 'flat', [])->willReturn([
            'name' => 'bar',
            'date_with_new_format' => '2020-01-01T00:00:00+00:00',
            'date_with_old_format' => '2020-01-01T00:00:00+00:00',
            'date_with_old_format_and_timezone' => '2020-01-01T12:00:00+12:00',
            'date_with_old_format_has_changed' => '2020-01-02T00:00:00+00:00'
        ]);

        $versionFactory->create(Argument::any(), 100, null, 'julia', null)->willReturn($version);

        $productModel->getId()->willReturn(100);

        $previousVersion->getVersion()->willReturn(1);
        $previousVersion->getSnapshot()->willReturn([
            'name' => 'foo',
            'date_with_new_format' => '2020-01-01T00:00:00+00:00',
            'date_with_old_format' => '2020-01-01',
            'date_with_old_format_and_timezone' => '2020-01-01',
            'date_with_old_format_has_changed' => '2020-01-01'
        ]);

        $version->setVersion(2)->willReturn($version);
        $version->setSnapshot([
            'name' => 'bar',
            'date_with_new_format' => '2020-01-01T00:00:00+00:00',
            'date_with_old_format' => '2020-01-01T00:00:00+00:00',
            'date_with_old_format_and_timezone' => '2020-01-01T12:00:00+12:00',
            'date_with_old_format_has_changed' => '2020-01-02T00:00:00+00:00'
        ])->willReturn($version);

        $version->setChangeset([
            'name' => ['old' => 'foo', 'new' => 'bar'],
            'date_with_old_format_has_changed' => ['old' => '2020-01-01', 'new' => '2020-01-02T00:00:00+00:00']
        ])->willReturn($version);

        $this->buildVersion($productModel, 'julia', $previousVersion, null);
    }

    function it_should_not_build_version_if_table_value_is_the_same_but_in_another_order(
        NormalizerInterface $normalizer,
        VersionFactory $versionFactory,
        ProductInterface $product,
        Version $previousVersion,
        Version $newVersion,
    ): void {
        $this->shouldSetChangeset(
            $product,
            $previousVersion,
            $normalizer,
            $versionFactory,
            $newVersion,
            '[{"per_100g":"300","per_serving":"12"}]',
            '[{"per_serving":"12","per_100g":"300"}]',
            []
        );

        $this->buildVersion($product, 'julia', $previousVersion);
    }

    function it_should_build_version_if_table_value_is_different(
        NormalizerInterface $normalizer,
        VersionFactory $versionFactory,
        ProductInterface $product,
        Version $previousVersion,
        Version $newVersion,
    ): void {
        $this->shouldSetChangeset(
            $product,
            $previousVersion,
            $normalizer,
            $versionFactory,
            $newVersion,
            '[{"per_100g":"300","per_serving":"12"}]',
            '[{"per_serving":"4000","per_100g":"300"}]',
            [
                'nutrition' => [
                    'old' => '[{"per_100g":"300","per_serving":"12"}]',
                    'new' => '[{"per_serving":"4000","per_100g":"300"}]',
                ]
            ]
        );
    }

    function it_should_build_version_if_table_value_line_is_added(
        NormalizerInterface $normalizer,
        VersionFactory $versionFactory,
        ProductInterface $product,
        Version $previousVersion,
        Version $newVersion,
    ): void {
        $this->shouldSetChangeset(
            $product,
            $previousVersion,
            $normalizer,
            $versionFactory,
            $newVersion,
            '[{"per_100g":"300","per_serving":"12"}]',
            '[{"per_100g":"300","per_serving":"12"}, {"per_100g":"3000","per_serving":"120"}]',
            [
                'nutrition' => [
                    'old' => '[{"per_100g":"300","per_serving":"12"}]',
                    'new' => '[{"per_100g":"300","per_serving":"12"}, {"per_100g":"3000","per_serving":"120"}]',
                ]
            ]
        );
    }

    private function shouldSetChangeset(
        ProductInterface $product,
        Version $previousVersion,
        NormalizerInterface $normalizer,
        VersionFactory $versionFactory,
        Version $newVersion,
        string $previousVersionValue,
        string $newVersionValue,
        array $expectedChangeset,
    ): void {
        $uuid = Uuid::uuid4();
        $product
            ->getUuid()
            ->shouldBeCalled()
            ->willReturn($uuid);

        $previousVersion
            ->getVersion()
            ->shouldBeCalled()
            ->willReturn(1);

        $previousVersion
            ->getSnapshot()
            ->shouldBeCalled()
            ->willReturn([
                'nutrition' => $previousVersionValue
            ]);

        $normalizer
            ->normalize($product, 'flat', [])
            ->shouldBeCalled()
            ->willReturn([
                'nutrition' => $newVersionValue
            ]);

        $versionFactory
            ->create(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($newVersion);

        $newVersion
            ->setVersion(2)
            ->shouldBeCalled()
            ->willReturn($newVersion);

        $newVersion
            ->setSnapshot([
                'nutrition' => $newVersionValue
            ])
            ->shouldBeCalled()
            ->willReturn($newVersion);

        $newVersion
            ->setChangeset($expectedChangeset)
            ->shouldBeCalled();

        $this->buildVersion($product, 'julia', $previousVersion);
    }
}
