<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ValidateLocalesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($channelRepository, $localeRepository);
    }

    public function it_validates_that_locales_exist_and_are_activated(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeEn->addChannel(new Channel());

        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEn);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $this->validate(['en_US', 'fr_FR'], null);
    }

    public function it_throws_exception_when_locale_does_not_exist(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());

        $localeRepository->findOneByIdentifier('en_US')->willReturn(null);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $this->shouldThrow(new InvalidQueryException('Locale "en_US" does not exist or is not activated.'))
            ->during('validate', [['en_US', 'fr_FR'], null]);
    }

    public function it_throws_exception_when_locale_is_not_activated(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $this->shouldThrow(new InvalidQueryException('Locale "fr_FR" does not exist or is not activated.'))
            ->during('validate', [['fr_FR'], null]);
    }

    public function it_throws_exception_when_locale_is_not_activated_for_the_provided_channel(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository
    ) {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');

        $channel = new Channel();
        $channel->setCode('tablet');
        $channel->addLocale($localeFr);

        $channel = new Channel();
        $channel->setCode('ecommerce');

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);

        $this->shouldThrow(new InvalidQueryException('Locale "fr_FR" is not activated for the scope "ecommerce".'))
            ->during('validate', [['fr_FR'], 'ecommerce']);
    }

    public function it_validates_that_all_locales_are_activated_for_the_provided_channel(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository
    ) {
        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeEn->addChannel(new Channel());

        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());

        $channel = new Channel();
        $channel->setCode('tablet');
        $channel->addLocale($localeFr);
        $channel->addLocale($localeEn);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEn);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);
        $channelRepository->findOneByIdentifier('tablet')->willReturn($channel);

        $this->validate(['en_US', 'fr_FR'], null);
    }
}
