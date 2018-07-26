<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

class ProductDraftSpec extends ObjectBehavior
{
    function it_gives_changes_for_attribute() {
        $this->setChanges([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => ['an english name']],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => ['a french name']]
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);

        $this->getChange('name', 'en_US', 'ecommerce')->shouldReturn(['an english name']);
    }

    function it_does_not_give_changes_for_unknown_attribute() {
        $this->getChange('name', 'en_US', 'ecommerce')->shouldReturn(null);
    }

    function it_remove_changes_for_an_attribute() {
        $this->setChanges([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);

        $this->removeChange('name', 'en_US', 'ecommerce');

        $this->getChanges()->shouldReturn([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);
    }

    function it_remove_changes_for_an_attribute_and_clean() {
        $this->setChanges([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);

        $this->removeChange('name', 'en_US', 'ecommerce');

        $this->getChanges()->shouldReturn(['values' => [], 'review_statuses' => []]);
    }

    function it_has_no_changes_by_default()
    {
        $this->hasChanges()->shouldReturn(false);
    }

    function it_has_no_changes_if_changes_values_are_empty()
    {
        $this->setChanges(['values' => []]);
        $this->hasChanges()->shouldReturn(false);
    }

    function it_has_changes_if_changes_values_are_not_empty()
    {
        $this->setChanges([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);

        $this->hasChanges()->shouldReturn(true);
    }

    function it_gives_changes_to_review()
    {
        $this->setChanges([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT]
                ]
            ]
        ]);

        $this->getChangesToReview()->shouldReturn([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT]
                ]
            ]
        ]);
    }
}
