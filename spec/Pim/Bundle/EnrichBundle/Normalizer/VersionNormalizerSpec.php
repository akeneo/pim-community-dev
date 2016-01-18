<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Model\Version;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class VersionNormalizerSpec extends ObjectBehavior
{
    function let(UserManager $userManager, TranslatorInterface $translator)
    {
        $this->beConstructedWith($userManager, $translator);
    }

    function it_supports_versions(Version $version)
    {
        $this->supportsNormalization($version, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_versions($userManager, Version $version, \DateTime $versionTime, User $steve)
    {
        $version->getId()->willReturn(12);
        $version->getResourceId()->willReturn(112);
        $version->getSnapshot()->willReturn('a nice snapshot');
        $version->getChangeset()->willReturn('the changeset');
        $version->getContext()->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->getVersion()->willReturn(12);
        $version->getLoggedAt()->willReturn($versionTime);
        $versionTime->format('Y-m-d H:i:s')->willReturn('1985-10-1 09:41:00');
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
            'logged_at'   => '1985-10-1 09:41:00',
            'pending'     => false
        ]);
    }

    function it_normalize_versions_with_deleted_user($userManager, $translator, Version $version, \DateTime $versionTime)
    {
        $version->getId()->willReturn(12);
        $version->getResourceId()->willReturn(112);
        $version->getSnapshot()->willReturn('a nice snapshot');
        $version->getChangeset()->willReturn('the changeset');
        $version->getContext()->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->getVersion()->willReturn(12);
        $version->getLoggedAt()->willReturn($versionTime);
        $versionTime->format('Y-m-d H:i:s')->willReturn('1985-10-1 09:41:00');
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
            'logged_at'   => '1985-10-1 09:41:00',
            'pending'     => false
        ]);
    }
}
