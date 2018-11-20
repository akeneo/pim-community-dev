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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory\InMemoryFamilySearchableRepository;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemoryFamilySearchableRepositorySpec extends ObjectBehavior
{
    public function let(FamilyRepositoryInterface $familyRepository): void
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_is_a_family_searchable_repository(): void
    {
        $this->shouldHaveType(InMemoryFamilySearchableRepository::class);
        $this->shouldImplement(FamilySearchableRepositoryInterface::class);
    }

    public function it_finds_all_family_without_filter($familyRepository): void
    {
        $family1 = (new Family())->setCode('a-family');
        $family2 = (new Family())->setCode('another-family');
        $family3 = (new Family())->setCode('again-another-family');

        $familyList = [$family1, $family2, $family3];
        $familyRepository->findAll()->willReturn($familyList);

        $this->findBySearch(0, 10)->shouldReturn($familyList);
    }

    public function it_finds_families_with_pagination_applied($familyRepository): void
    {
        $family1 = (new Family())->setCode('a-family');
        $family2 = (new Family())->setCode('another-family');
        $family3 = (new Family())->setCode('again-another-family');

        $familyRepository->findAll()->willReturn([$family1, $family2, $family3]);

        $this->findBySearch(0, 1)->shouldReturn([$family1]);
        $this->findBySearch(1, 1)->shouldReturn([$family2]);
        $this->findBySearch(2, 1)->shouldReturn([$family3]);
    }

    public function it_finds_families_with_search_applied_on_code_and_label(
        $familyRepository,
        FamilyTranslationInterface $family1LabelTranslation,
        FamilyTranslationInterface $family2LabelTranslation,
        FamilyTranslationInterface $family3LabelTranslation
    ): void {
        $family1LabelTranslation->getLabel()->willReturn('family1');
        $family1LabelTranslation->getLocale()->willReturn('en_US');
        $family2LabelTranslation->getLabel()->willReturn('family2');
        $family2LabelTranslation->getLocale()->willReturn('en_US');
        $family3LabelTranslation->getLabel()->willReturn('another-family');
        $family3LabelTranslation->getLocale()->willReturn('en_US');

        $family1 = (new Family())
            ->setCode('a-family')
            ->addTranslation($family1LabelTranslation->getWrappedObject())
            ->setLocale('en_US');
        $family2 = (new Family())
            ->setCode('another-family')
            ->addTranslation($family2LabelTranslation->getWrappedObject())
            ->setLocale('en_US');
        $family3 = (new Family())
            ->setCode('third-family')
            ->addTranslation($family3LabelTranslation->getWrappedObject())
            ->setLocale('en_US');

        $familyRepository->findAll()->willReturn([$family1, $family2, $family3]);

        $this->findBySearch(0, 10, 'another')->shouldReturn([
            1 => $family2,
            2 => $family3,
        ]);
    }
}
