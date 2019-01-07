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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryFamilyRepository;
use Akeneo\Pim\Structure\Component\Model\Family as StructureFamily;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface as StructureFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemoryFamilyRepositorySpec extends ObjectBehavior
{
    public function let(StructureFamilyRepositoryInterface $familyRepository): void
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_is_an_attribute_mapping_family_repository(): void
    {
        $this->shouldImplement(FamilyRepositoryInterface::class);
    }

    public function it_is_the_in_memory_implementation_of_the_family_repository(): void
    {
        $this->shouldHaveType(InMemoryFamilyRepository::class);
    }

    public function it_finds_all_family_without_filter($familyRepository): void
    {
        $family1 = (new StructureFamily())->setCode('a_family');
        $family2 = (new StructureFamily())->setCode('another_family');
        $family3 = (new StructureFamily())->setCode('again_another_family');

        $familyList = [$family1, $family2, $family3];
        $familyRepository->findAll()->willReturn($familyList);

        $familyCollection = $this->findBySearch(0, 10, null, []);
        $familyCollection->shouldHaveTheFollowingFamilies(['a_family', 'another_family', 'again_another_family']);
    }

    public function it_finds_families_with_pagination_applied($familyRepository): void
    {
        $family1 = (new StructureFamily())->setCode('a_family');
        $family2 = (new StructureFamily())->setCode('another_family');
        $family3 = (new StructureFamily())->setCode('again_another_family');

        $familyRepository->findAll()->willReturn([$family1, $family2, $family3]);

        $familyCollection1 = $this->findBySearch(0, 1, null, []);
        $familyCollection1->shouldHaveTheFollowingFamilies(['a_family']);

        $familyCollection2 = $this->findBySearch(1, 1, null, []);
        $familyCollection2->shouldHaveTheFollowingFamilies(['another_family']);

        $familyCollection3 = $this->findBySearch(2, 1, null, []);
        $familyCollection3->shouldHaveTheFollowingFamilies(['again_another_family']);
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
        $family3LabelTranslation->getLabel()->willReturn('another_family');
        $family3LabelTranslation->getLocale()->willReturn('en_US');

        $family1 = (new StructureFamily())
            ->setCode('a_family')
            ->addTranslation($family1LabelTranslation->getWrappedObject())
            ->setLocale('en_US');
        $family2 = (new StructureFamily())
            ->setCode('another_family')
            ->addTranslation($family2LabelTranslation->getWrappedObject())
            ->setLocale('en_US');
        $family3 = (new StructureFamily())
            ->setCode('third_family')
            ->addTranslation($family3LabelTranslation->getWrappedObject())
            ->setLocale('en_US');

        $familyRepository->findAll()->willReturn([$family1, $family2, $family3]);

        $familyCollection = $this->findBySearch(0, 10, 'another', []);
        $familyCollection->shouldHaveTheFollowingFamilies(['another_family', 'third_family']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'haveTheFollowingFamilies' => function (FamilyCollection $subject, array $expectedFamilyCodes) {
                if (count($expectedFamilyCodes) !== $subject->getIterator()->count()) {
                    return false;
                }

                foreach ($subject as $positionInList => $family) {
                    if ($family->getCode() !== $expectedFamilyCodes[$positionInList]) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
