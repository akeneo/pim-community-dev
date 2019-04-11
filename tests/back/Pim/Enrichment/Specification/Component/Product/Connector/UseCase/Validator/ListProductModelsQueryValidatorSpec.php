<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedCategories;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedLocales;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedProperties;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedSearchLocale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCategories;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateChannel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCriterion;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateLocales;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidatePagination;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateProperties;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateSearchLocale;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ListProductModelsQueryValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            new ValidateAttributes($attributeRepository->getWrappedObject()),
            new ValidateChannel($channelRepository->getWrappedObject()),
            new ValidateLocales($channelRepository->getWrappedObject(), $localeRepository->getWrappedObject()),
            new ValidatePagination(new PaginationParametersValidator(['pagination' =>['limit_max' => 100]])),
            new ValidateCriterion(),
            new ValidateCategories($categoryRepository->getWrappedObject()),
            new ValidateProperties($attributeRepository->getWrappedObject()),
            new ValidateSearchLocale($localeRepository->getWrappedObject()),
            new ValidateAlwaysGrantedSearchLocale(),
            new ValidateAlwaysGrantedCategories(),
            new ValidateAlwaysGrantedProperties(),
            new ValidateAlwaysGrantedAttributes(),
            new ValidateAlwaysGrantedLocales()
        );

        $channel = new Channel();
        $localeFR = new Locale();
        $localeFR->addChannel($channel);
        $localeFR->setCode('fr_FR');
        $localeUS = new Locale();
        $localeUS->addChannel($channel);
        $localeUS->setCode('en_US');
        $channel->addLocale($localeFR);
        $channel->addLocale($localeUS);

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFR);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeUS);
        $channelRepository->findOneByIdentifier('tablet')->willReturn($channel);
        $attributeRepository->findOneByIdentifier('name')->willReturn(new Attribute());
    }

    function it_validates_the_command_query()
    {
        $query = new ListProductModelsQuery();
        $query->localeCodes = ['en_US', 'fr_FR'];
        $query->searchLocaleCode = 'fr_FR';
        $query->channelCode = 'tablet';
        $query->searchChannelCode = 'tablet';
        $query->attributeCodes = ['name'];
        $query->limit = 10;
        $query->page = 1;
        $query->userId = 1;
        $query->search = ['name' => [['operator' => 'EQUALS', 'value' => 'michel']]];

        $this->validate($query);
    }
}
