<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Locale;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

final class AddDefaultDictionaryWordsSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository
    ) {
        $this->beConstructedWith($dataQualityInsightsFeature, $textCheckerDictionaryRepository);
    }

    public function it_does_nothing_if_the_subject_is_not_a_locale($textCheckerDictionaryRepository)
    {
        $textCheckerDictionaryRepository->save(Argument::any())->shouldNotBeCalled();

        $this->onSaveLocale(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_if_the_locale_is_not_activated($textCheckerDictionaryRepository)
    {
        $locale = $this->givenADeactivatedLocale();

        $textCheckerDictionaryRepository->save(Argument::any())->shouldNotBeCalled();

        $this->onSaveLocale(new GenericEvent($locale));
    }

    public function it_does_nothing_if_the_dqi_feature_is_disabled(
        $textCheckerDictionaryRepository,
        $dataQualityInsightsFeature
    ) {
        $locale = $this->givenAnActivatedLocale();

        $textCheckerDictionaryRepository->save(Argument::any())->shouldNotBeCalled();
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $this->onSaveLocale(new GenericEvent($locale));
    }

    public function it_saves_default_words_in_the_dictionary_when_a_locales_is_activated(
        $textCheckerDictionaryRepository,
        $dataQualityInsightsFeature
    ) {
        $locale = $this->givenAnActivatedLocale();
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $textCheckerDictionaryRepository->saveAll(Argument::that(function (array $words) {
            foreach ($words as $word) {
                if (!$word instanceof Write\TextCheckerDictionaryWord) {
                    return false;
                }
            }

            return true;
        }))->shouldBeCalled();

        $this->onSaveLocale(new GenericEvent($locale));
    }

    private function givenAnActivatedLocale(): LocaleInterface
    {
        $channel = (new Channel())->setCode('mobile');

        return (new Locale())
            ->setCode('en_US')
            ->addChannel($channel);
    }

    private function givenADeactivatedLocale(): LocaleInterface
    {
        return (new Locale())->setCode('en_US');
    }
}
