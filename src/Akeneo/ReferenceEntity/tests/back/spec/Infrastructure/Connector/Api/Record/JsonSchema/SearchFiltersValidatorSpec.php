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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\SearchFiltersValidator;
use PhpSpec\ObjectBehavior;

class SearchFiltersValidatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SearchFiltersValidator::class);
    }

    public function it_returns_an_empty_array_if_the_given_search_filters_are_valid()
    {
        $searchFilters = [
            'complete' => [
                'operator' => '=',
                'value' => true,
                'channel' => 'mobile',
                'locales' => ['en_US', 'fr_FR'],
            ],
            'updated' => [
                [
                    'operator' => '<',
                    'value' => '2020-01-01T10:00:00+00:00',
                ],
                [
                    'operator' => '>',
                    'value' => '2018-01-01T10:00:00+00:00',
                ],
            ],
            'code' => [
                [
                    'operator' => 'IN',
                    'value' => ['123'],
                ],
            ],
        ];

        $this->validate($searchFilters)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_given_completeness_search_filters_are_valid()
    {
        $searchFilters = [
            'complete' => [
                'operator' => '=',
                'value' => false,
                'channel' => 'mobile',
                'locales' => ['en_US'],
            ],
        ];

        $this->validate($searchFilters)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_given_updated_search_filters_are_valid()
    {
        $searchFilters = [
            'updated' => [
                [
                    'operator' => '<',
                    'value' => '2020-01-01T10:00:00+00:00',
                ],
                [
                    'operator' => '>',
                    'value' => '2018-01-01T10:00:00+00:00',
                ],
            ],
        ];

        $this->validate($searchFilters)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_given_updated_between_search_filters_are_valid()
    {
        $searchFilters = [
            'updated' => [
                [
                    'operator' => 'BETWEEN',
                    'value' => ['2018-01-01T10:00:00+00:00', '2020-01-01T10:00:00+00:00'],
                ],
            ],
        ];

        $this->validate($searchFilters)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_given_updated_since_search_filters_are_valid()
    {
        $searchFilters = [
            'updated' => [
                [
                    'operator' => 'SINCE LAST N DAYS',
                    'value' => 1,
                ],
            ],
        ];

        $this->validate($searchFilters)->shouldReturn([]);
    }

    public function it_returns_an_error_when_the_operator_of_the_completeness_filter_is_not_supported()
    {
        $invalidSearchFilters = [
            'complete' => [
                'operator' => '>',
                'value' => true,
                'channel' => 'mobile',
                'locales' => ['en_US', 'fr_FR'],
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    public function it_returns_an_error_when_the_value_of_the_completeness_filter_is_not_a_boolean()
    {
        $invalidSearchFilters = [
            'complete' => [
                'operator' => '=',
                'value' => '100',
                'channel' => 'mobile',
                'locales' => ['en_US', 'fr_FR'],
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    public function it_returns_an_error_when_a_property_of_the_completeness_filter_is_missing()
    {
        $invalidSearchFilters = [
            'complete' => [
                'operator' => '=',
                'value' => true,
                'locales' => ['en_US', 'fr_FR'],
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    public function it_returns_an_error_when_no_locales_are_given_for_the_completeness_filter()
    {
        $invalidSearchFilters = [
            'complete' => [
                'operator' => '=',
                'value' => true,
                'channel' => 'mobile',
                'locales' => [],
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    public function it_returns_an_error_when_the_operator_of_the_updated_filter_is_not_supported()
    {
        $invalidSearchFilters = [
            'updated' => [
                'operator' => '=',
                'value' => '2018-10-14T10:00:00+00:00',
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    public function it_returns_an_error_when_the_value_of_the_updated_filter_is_not_a_date_time()
    {
        $invalidSearchFilters = [
            'updated' => [
                'operator' => '>',
                'value' => 'abc123',
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    public function it_returns_all_the_errors_of_invalid_search_filters()
    {
        $invalidSearchFilters = [
            'complete' => [
                'operator' => '<',
                'value' => '100',
                'locales' => 42,
            ],
        ];

        $errors = $this->validate($invalidSearchFilters);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }
}
