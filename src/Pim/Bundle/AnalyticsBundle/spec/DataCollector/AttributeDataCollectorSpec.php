<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Pim\Bundle\AnalyticsBundle\DataCollector\AttributeDataCollector;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountLocalizableAttribute;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountScopableAndLocalizableAttribute;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountScopableAttribute;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

class AttributeDataCollectorSpec extends ObjectBehavior
{
    function let(
        CountQuery $attributeCountQuery,
        CountQuery $localizableAttributeCountQuery,
        CountQuery $scopableAttributeCountQuery,
        CountQuery $localizableAndScopableAttributeCountQuery
    ) {
        $this->beConstructedWith(
            $attributeCountQuery,
            $localizableAttributeCountQuery,
            $scopableAttributeCountQuery,
            $localizableAndScopableAttributeCountQuery
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeDataCollector::class);
    }

    function it_is_a_data_collector()
    {
        $this->shouldImplement(DataCollectorInterface::class);
    }

    function it_collects_data_about_catalog(
        $attributeCountQuery,
        $localizableAttributeCountQuery,
        $scopableAttributeCountQuery,
        $localizableAndScopableAttributeCountQuery
    ) {
        $attributeCountQuery->fetch()->willReturn(new CountVolume(1000, -1, 'count_attributes'));
        $localizableAttributeCountQuery->fetch()->willReturn(new CountVolume(33, -1, 'count_localizable_attributes'));
        $scopableAttributeCountQuery->fetch()->willReturn(new CountVolume(40, -1, 'count_scopable_attributes'));
        $localizableAndScopableAttributeCountQuery->fetch()->willReturn(new CountVolume(64, -1, 'count_localizable_and_scopable_attributes'));

        $this->collect()->shouldReturn([
            'nb_attributes' => 1000,
            'nb_scopable_attributes' => 40,
            'nb_localizable_attributes' => 33,
            'nb_scopable_localizable_attributes' => 64,
        ]);
    }
}
