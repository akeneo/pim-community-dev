<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandlerSpec extends ObjectBehavior
{
    public function let(FamilySearchableRepositoryInterface $familyRepository)
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_is_a_get_families_query()
    {
        $this->shouldHaveType(SearchFamiliesHandler::class);
    }

    public function it_handles_a_get_families_query(
        FamilyInterface $family1,
        FamilyTranslationInterface $family1Translation,
        \Iterator $family1TranslationsIterator,
        Collection $family1Translations,
        FamilyInterface $family2,
        FamilyTranslationInterface $family2Translation,
        \Iterator $family2TranslationsIterator,
        Collection $family2Translations,
        $familyRepository
    ) {
        $family1Translations->getIterator()->willReturn($family1TranslationsIterator);
        $family1TranslationsIterator->rewind()->shouldBeCalled();
        $family1TranslationsIterator->valid()->willReturn(true, false);
        $family1TranslationsIterator->current()->willReturn($family1Translation);
        $family1TranslationsIterator->next()->shouldBeCalled();

        $family1->getCode()->willReturn('router');
        $family1Translation->getLocale()->willReturn('en_US');
        $family1Translation->getLabel()->willReturn('router');
        $family1->getTranslations()->willReturn($family1Translations);

        $family2Translations->getIterator()->willReturn($family2TranslationsIterator);
        $family2TranslationsIterator->rewind()->shouldBeCalled();
        $family2TranslationsIterator->valid()->willReturn(true, false);
        $family2TranslationsIterator->current()->willReturn($family2Translation);
        $family2TranslationsIterator->next()->shouldBeCalled();

        $family2->getCode()->willReturn('camcorders');
        $family2Translation->getLocale()->willReturn('en_US');
        $family2Translation->getLabel()->willReturn('camcorders');
        $family2->getTranslations()->willReturn($family2Translations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([
            $family1,
            $family2,
        ]);

        $familyCollection = $this->handle($query);
        $familyCollection->getIterator()->count()->shouldReturn(2);
    }
}
