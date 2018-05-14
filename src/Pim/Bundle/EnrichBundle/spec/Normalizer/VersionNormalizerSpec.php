<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Manager\UserManager;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;
use Pim\Component\User\Model\User;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class VersionNormalizerSpec extends ObjectBehavior
{
    function let(
        UserManager $userManager,
        TranslatorInterface $translator,
        PresenterInterface $datetimePresenter,
        PresenterRegistryInterface $presenterRegistry
    ) {
        $this->beConstructedWith($userManager, $translator, $datetimePresenter, $presenterRegistry);
    }

    function it_supports_versions(Version $version)
    {
        $this->supportsNormalization($version, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_versions(
        $userManager,
        $translator,
        $datetimePresenter,
        $presenterRegistry,
        Version $version,
        User $steve,
        PresenterInterface $numberPresenter,
        PresenterInterface $pricesPresenter,
        PresenterInterface $metricPresenter
    ) {
        $versionTime = new \DateTime();

        $changeset = [
            'maximum_frame_rate' => ['old' => '', 'new' => '200.7890'],
            'price-EUR'          => ['old' => '5.00', 'new' => '5.15'],
            'weight'             => ['old' => '', 'new' => '10.1234'],
        ];

        $version->getId()->willReturn(12);
        $version->getResourceId()->willReturn(112);
        $version->getSnapshot()->willReturn('a nice snapshot');
        $version->getChangeset()->willReturn($changeset);
        $version->getContext()->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->getVersion()->willReturn(12);
        $version->getLoggedAt()->willReturn($versionTime);
        $translator->getLocale()->willReturn('en_US');
        $datetimePresenter->present($versionTime, Argument::any())->willReturn('01/01/1985 09:41 AM');
        $version->isPending()->willReturn(false);

        $version->getAuthor()->willReturn('steve');
        $userManager->findUserByUsername('steve')->willReturn($steve);
        $steve->getFirstName()->willReturn('Steve');
        $steve->getLastName()->willReturn('Jobs');

        $changeset = [
            'maximum_frame_rate' => ['old' => '', 'new' => '200,7890'],
            'price-EUR'          => ['old' => '5,00 €', 'new' => '5,15 €'],
            'weight'             => ['old' => '', 'new' => '10,1234'],
        ];

        $options = ['locale' => 'fr_FR'];
        $translator->getLocale()->willReturn('fr_FR');

        $presenterRegistry->getPresenterByAttributeCode('maximum_frame_rate')->willReturn($numberPresenter);
        $presenterRegistry->getPresenterByAttributeCode('price')->willReturn($pricesPresenter);
        $presenterRegistry->getPresenterByAttributeCode('weight')->willReturn($metricPresenter);

        $numberPresenter
            ->present('200.7890', $options + ['versioned_attribute' => 'maximum_frame_rate'])
            ->willReturn('200,7890');
        $pricesPresenter->present('5.00', $options + ['versioned_attribute' => 'price-EUR'])->willReturn('5,00 €');
        $pricesPresenter->present('5.15', $options + ['versioned_attribute' => 'price-EUR'])->willReturn('5,15 €');
        $metricPresenter->present('10.1234', $options + ['versioned_attribute' => 'weight'])->willReturn('10,1234');

        $numberPresenter->present('', $options + ['versioned_attribute' => 'maximum_frame_rate'])->willReturn('');
        $pricesPresenter->present('', $options)->willReturn('');
        $metricPresenter->present('', $options + ['versioned_attribute' => 'weight'])->willReturn('');

        $this->normalize($version, 'internal_api')->shouldReturn([
            'id'          => 12,
            'author'      => 'Steve Jobs',
            'resource_id' => '112',
            'snapshot'    => 'a nice snapshot',
            'changeset'   => $changeset,
            'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
            'version'     => 12,
            'logged_at'   => '01/01/1985 09:41 AM',
            'pending'     => false
        ]);
    }

    function it_normalize_versions_with_deleted_user(
        $userManager,
        $translator,
        $datetimePresenter,
        Version $version
    ) {
        $versionTime = new \DateTime();

        $version->getId()->willReturn(12);
        $version->getResourceId()->willReturn(112);
        $version->getSnapshot()->willReturn('a nice snapshot');
        $version->getChangeset()->willReturn(['text' => 'the changeset']);
        $version->getContext()->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->getVersion()->willReturn(12);
        $version->getLoggedAt()->willReturn($versionTime);
        $translator->getLocale()->willReturn('en_US');
        $datetimePresenter->present($versionTime, Argument::any())->willReturn('01/01/1985 09:41 AM');
        $version->isPending()->willReturn(false);

        $version->getAuthor()->willReturn('steve');
        $userManager->findUserByUsername('steve')->willReturn(null);

        $translator->trans('Removed user')->willReturn('Utilisateur supprimé');

        $this->normalize($version, 'internal_api')->shouldReturn([
            'id'          => 12,
            'author'      => 'steve - Utilisateur supprimé',
            'resource_id' => '112',
            'snapshot'    => 'a nice snapshot',
            'changeset'   => ['text' => 'the changeset'],
            'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
            'version'     => 12,
            'logged_at'   => '01/01/1985 09:41 AM',
            'pending'     => false
        ]);
    }
}
