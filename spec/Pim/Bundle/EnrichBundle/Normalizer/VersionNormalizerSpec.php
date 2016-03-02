<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Component\Versioning\Model\Version;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class VersionNormalizerSpec extends ObjectBehavior
{
    function let(
        UserManager $userManager,
        TranslatorInterface $translator,
        PresenterInterface $datetimePresenter
    ) {
        $this->beConstructedWith($userManager, $translator, $datetimePresenter);
    }

    function it_supports_versions(Version $version)
    {
        $this->supportsNormalization($version, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_versions(
        $userManager,
        $translator,
        $datetimePresenter,
        Version $version,
        User $steve
    ) {
        $versionTime = new \DateTime();

        $version->getId()->willReturn(12);
        $version->getResourceId()->willReturn(112);
        $version->getSnapshot()->willReturn('a nice snapshot');
        $version->getChangeset()->willReturn('the changeset');
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
        $steve->getEmail()->willReturn('steve@pear.com');

        $this->normalize($version, 'internal_api')->shouldReturn([
            'id'          => 12,
            'author'      => 'Steve Jobs - steve@pear.com',
            'resource_id' => '112',
            'snapshot'    => 'a nice snapshot',
            'changeset'   => 'the changeset',
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
        $version->getChangeset()->willReturn('the changeset');
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
            'changeset'   => 'the changeset',
            'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
            'version'     => 12,
            'logged_at'   => '01/01/1985 09:41 AM',
            'pending'     => false
        ]);
    }
}
