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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandlerSpec extends ObjectBehavior
{
    public function let(
        FamilySearchableRepositoryInterface $familyRepository,
        AttributesMappingProviderInterface $attributesMappingProvider
    ): void {
        $this->beConstructedWith($familyRepository, $attributesMappingProvider);
    }

    public function it_is_a_get_families_query(): void
    {
        $this->shouldHaveType(SearchFamiliesHandler::class);
    }

    public function it_handles_a_get_families_query_with_pending_attributes(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family1,
        FamilyTranslationInterface $family1Translation,
        \Iterator $family1TranslationsIterator,
        Collection $family1Translations,
        FamilyInterface $family2,
        FamilyTranslationInterface $family2Translation,
        \Iterator $family2TranslationsIterator,
        Collection $family2Translations
    ): void {
        $nameAttrMapping = new AttributeMapping('name', null, 'text', null, AttributeMapping::ATTRIBUTE_PENDING, []);
        $titleAttrMapping = new AttributeMapping('title', null, 'text', null, AttributeMapping::ATTRIBUTE_PENDING, []);
        $routerAttributesMappingResponse = new AttributesMappingResponse();
        $routerAttributesMappingResponse->addAttribute($nameAttrMapping);
        $routerAttributesMappingResponse->addAttribute($titleAttrMapping);
        $camcordersAttributesMappingResponse = new AttributesMappingResponse();
        $camcordersAttributesMappingResponse->addAttribute($nameAttrMapping);

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($routerAttributesMappingResponse);
        $attributesMappingProvider
            ->getAttributesMapping('camcorders')
            ->willReturn($camcordersAttributesMappingResponse);

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
        $familyCollection->shouldHaveFamilyTimes(2);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_PENDING);
        $familyCollection->shouldHaveFamilyWithMappingStatus('camcorders', Family::MAPPING_PENDING);
    }

    public function it_handles_a_get_families_query_with_at_least_one_pending_attribute(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family,
        FamilyTranslationInterface $familyTranslation,
        \Iterator $familyTranslationsIterator,
        Collection $familyTranslations
    ): void {
        $nameAttrMapping = new AttributeMapping('name', null, 'text', null, AttributeMapping::ATTRIBUTE_MAPPED, []);
        $titleAttrMapping = new AttributeMapping('title', null, 'text', null, AttributeMapping::ATTRIBUTE_UNMAPPED, []);
        $descAttrMapping = new AttributeMapping('desc', null, 'text', null, AttributeMapping::ATTRIBUTE_PENDING, []);
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute($nameAttrMapping);
        $attributesMappingResponse->addAttribute($titleAttrMapping);
        $attributesMappingResponse->addAttribute($descAttrMapping);

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($attributesMappingResponse);

        $familyTranslations->getIterator()->willReturn($familyTranslationsIterator);
        $familyTranslationsIterator->rewind()->shouldBeCalled();
        $familyTranslationsIterator->valid()->willReturn(true, false);
        $familyTranslationsIterator->current()->willReturn($familyTranslation);
        $familyTranslationsIterator->next()->shouldBeCalled();

        $family->getCode()->willReturn('router');
        $familyTranslation->getLocale()->willReturn('en_US');
        $familyTranslation->getLabel()->willReturn('router');
        $family->getTranslations()->willReturn($familyTranslations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([$family]);

        $familyCollection = $this->handle($query);
        $familyCollection->shouldHaveFamilyTimes(1);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_PENDING);
    }

    public function it_handles_a_get_families_query_with_mapped_attributes(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family,
        FamilyTranslationInterface $familyTranslation,
        \Iterator $familyTranslationsIterator,
        Collection $familyTranslations
    ): void {
        $nameAttrMapping = new AttributeMapping('name', null, 'text', null, AttributeMapping::ATTRIBUTE_MAPPED, []);
        $titleAttrMapping = new AttributeMapping('title', null, 'text', null, AttributeMapping::ATTRIBUTE_MAPPED, []);
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute($nameAttrMapping);
        $attributesMappingResponse->addAttribute($titleAttrMapping);

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($attributesMappingResponse);

        $familyTranslations->getIterator()->willReturn($familyTranslationsIterator);
        $familyTranslationsIterator->rewind()->shouldBeCalled();
        $familyTranslationsIterator->valid()->willReturn(true, false);
        $familyTranslationsIterator->current()->willReturn($familyTranslation);
        $familyTranslationsIterator->next()->shouldBeCalled();

        $family->getCode()->willReturn('router');
        $familyTranslation->getLocale()->willReturn('en_US');
        $familyTranslation->getLabel()->willReturn('router');
        $family->getTranslations()->willReturn($familyTranslations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([$family]);

        $familyCollection = $this->handle($query);
        $familyCollection->shouldHaveFamilyTimes(1);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_FULL);
    }

    public function it_handles_a_get_families_query_with_unmapped_attributes(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family,
        FamilyTranslationInterface $familyTranslation,
        \Iterator $familyTranslationsIterator,
        Collection $familyTranslations
    ): void {
        $nameAttrMapping = new AttributeMapping('name', null, 'text', null, AttributeMapping::ATTRIBUTE_UNMAPPED, []);
        $titleAttrMapping = new AttributeMapping('title', null, 'text', null, AttributeMapping::ATTRIBUTE_UNMAPPED, []);
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute($nameAttrMapping);
        $attributesMappingResponse->addAttribute($titleAttrMapping);

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($attributesMappingResponse);

        $familyTranslations->getIterator()->willReturn($familyTranslationsIterator);
        $familyTranslationsIterator->rewind()->shouldBeCalled();
        $familyTranslationsIterator->valid()->willReturn(true, false);
        $familyTranslationsIterator->current()->willReturn($familyTranslation);
        $familyTranslationsIterator->next()->shouldBeCalled();

        $family->getCode()->willReturn('router');
        $familyTranslation->getLocale()->willReturn('en_US');
        $familyTranslation->getLabel()->willReturn('router');
        $family->getTranslations()->willReturn($familyTranslations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([$family]);

        $familyCollection = $this->handle($query);
        $familyCollection->shouldHaveFamilyTimes(1);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_FULL);
    }

    public function it_handles_a_get_families_query_with_mapped_and_unmapped_attributes(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family,
        FamilyTranslationInterface $familyTranslation,
        \Iterator $familyTranslationsIterator,
        Collection $familyTranslations
    ): void {
        $nameAttrMapping = new AttributeMapping('name', null, 'text', null, AttributeMapping::ATTRIBUTE_MAPPED, []);
        $titleAttrMapping = new AttributeMapping('title', null, 'text', null, AttributeMapping::ATTRIBUTE_UNMAPPED, []);
        $attributesMappingResponse = new AttributesMappingResponse();
        $attributesMappingResponse->addAttribute($nameAttrMapping);
        $attributesMappingResponse->addAttribute($titleAttrMapping);

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($attributesMappingResponse);

        $familyTranslations->getIterator()->willReturn($familyTranslationsIterator);
        $familyTranslationsIterator->rewind()->shouldBeCalled();
        $familyTranslationsIterator->valid()->willReturn(true, false);
        $familyTranslationsIterator->current()->willReturn($familyTranslation);
        $familyTranslationsIterator->next()->shouldBeCalled();

        $family->getCode()->willReturn('router');
        $familyTranslation->getLocale()->willReturn('en_US');
        $familyTranslation->getLabel()->willReturn('router');
        $family->getTranslations()->willReturn($familyTranslations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([$family]);

        $familyCollection = $this->handle($query);
        $familyCollection->shouldHaveFamilyTimes(1);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_FULL);
    }

    public function it_handles_a_get_families_query_with_no_attribute(
        $familyRepository,
        $attributesMappingProvider,
        FamilyInterface $family,
        FamilyTranslationInterface $familyTranslation,
        \Iterator $familyTranslationsIterator,
        Collection $familyTranslations
    ): void {
        $attributesMappingResponse = new AttributesMappingResponse();

        $attributesMappingProvider->getAttributesMapping('router')->willReturn($attributesMappingResponse);

        $familyTranslations->getIterator()->willReturn($familyTranslationsIterator);
        $familyTranslationsIterator->rewind()->shouldBeCalled();
        $familyTranslationsIterator->valid()->willReturn(true, false);
        $familyTranslationsIterator->current()->willReturn($familyTranslation);
        $familyTranslationsIterator->next()->shouldBeCalled();

        $family->getCode()->willReturn('router');
        $familyTranslation->getLocale()->willReturn('en_US');
        $familyTranslation->getLabel()->willReturn('router');
        $family->getTranslations()->willReturn($familyTranslations);

        $query = new SearchFamiliesQuery(10, 1, [], 'search_text');

        $familyRepository->findBySearch(1, 10, 'search_text', [])->willReturn([$family]);

        $familyCollection = $this->handle($query);
        $familyCollection->shouldHaveFamilyTimes(1);
        $familyCollection->shouldHaveFamilyWithMappingStatus('router', Family::MAPPING_EMPTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'haveFamilyTimes' => function (FamilyCollection $subject, $numberInCollection) {
                return $numberInCollection === $subject->getIterator()->count();
            },
            'haveFamilyWithMappingStatus' => function (
                FamilyCollection $subject,
                string $familyCode,
                int $mappingStatus
            ) {
                foreach ($subject as $family) {
                    if ($familyCode === $family->getCode()) {
                        return $mappingStatus === $family->getMappingStatus();
                    }
                }

                return false;
            },
        ];
    }
}
