<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslation;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;

class TranslationsUpdateGuesserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['stdClass']);
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TranslationsUpdateGuesser::class);
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_supports_delete_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction('bar')->shouldReturn(false);
    }

    function it_guesses_translatable_entity_updates(
        Category $category,
        CategoryTranslation $translation,
        EntityManager $em,
        UnitOfWork $uow
    ) {
        $translation->getForeignKey()->willReturn($category);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($category)->willReturn(UnitOfWork::STATE_MANAGED);

        $this->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$category]);
    }

    function it_returns_no_pending_updates_if_entity_state_is_removed(
        EntityManager $em,
        UnitOfWork $uow,
        Category $category
    ) {
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($category)->willReturn(UnitOfWork::STATE_REMOVED);

        $this->guessUpdates($em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }

    function it_returns_no_pending_updates_if_not_given_versionable_class(
        CategoryTranslation $translation,
        EntityManager $em,
        UnitOfWork $uow,
        LocaleInterface $locale
    ) {
        $translation->getForeignKey()->willReturn($locale);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($locale)->willReturn(UnitOfWork::STATE_REMOVED);

        $this->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
