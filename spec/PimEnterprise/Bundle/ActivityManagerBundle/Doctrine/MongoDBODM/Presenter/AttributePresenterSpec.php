<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Presenter\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\MongoDBODM\Presenter\AttributePresenter;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

class AttributePresenterSpec extends ObjectBehavior
{
    function let(AttributeGroupRepositoryInterface $attributeGroupRepository)
    {
        $this->beConstructedWith($attributeGroupRepository);
    }

    function it_is_a_presenter()
    {
        $this->shouldImplement(PresenterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributePresenter::class);
    }

    function it_presents_simple_attributes()
    {
        $mandatoryAttributes = [
            "sku" => 1481534557,
            "name" => "Mug",
        ];

        $results = [
            'general' => [
                'sku',
            ],
            'marketing' => [
                'name',
            ],
        ];

        $this->present($mandatoryAttributes)->shouldReturn($results);
    }

    function it_presents_simple_scopable_attributes()
    {
        $mandatoryAttributes = [
            "name-en_US-ecommerce" => "Mug",
            "name-en_US-print" => "Mug print",
            "description-en_US-print" => "Mug print",
        ];

        $results = [
            'marketing' => [
                'name',
            ],
        ];

        $this->present($mandatoryAttributes, ['channel' => "ecommerce", "locale" => "en_US"])->shouldReturn($results);
    }

    function it_presents_simple_localizable_attributes()
    {
        $mandatoryAttributes = [
            "name-en_US-ecommerce" => "Mug",
            "name-fr_FR-ecommerce" => "Tasse",
            "description-en_US-ecommerce" => "Awesome mug",
        ];

        $results = [
            'marketing' => [
                'name',
            ],
        ];

        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_simple_price_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_localizable_price_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_scopable_price_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_simple_metric_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_localizable_metric_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_scopable_metric_attributes()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_simple_attribute_options()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_localizable_attribute_options()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }

    function it_presents_scopable_attribute_options()
    {
        $this->present($mandatoryAttributes, ['locale' => "fr_FR", "channel" => "ecommerce"])->shouldReturn($results);
    }
}
