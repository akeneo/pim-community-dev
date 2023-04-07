<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ByChannelAndLocalesFilterSpec extends ObjectBehavior
{
    public function it_returns_the_list_of_enriched_values_to_clean_while_cleaning_channel(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());

        $this->getEnrichedValuesToClean($valuesToFilter, 'mobile', [])->shouldReturn(
            [
                $valuesToFilter->getValue(
                    'long_description',
                    'c91e6a4e-733b-4d77-aefc-129edbf03233',
                    'mobile',
                    'fr_FR'
                ),
                $valuesToFilter->getValue(
                    'long_description',
                    'c91e6a4e-733b-4d77-aefc-129edbf03233',
                    'mobile',
                    'en_US'
                )
            ]
        );
    }

    public function it_does_nothing_when_deleted_channel_code_is_null(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());

        $this->getEnrichedValuesToClean($valuesToFilter, '', [])->shouldReturn([]);
    }

    public function it_returns_the_list_of_enriched_values_to_clean_while_cleaning_locales(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());

        $this->getEnrichedValuesToClean($valuesToFilter, 'mobile', ['en_US'])->shouldReturn(
            [
                $valuesToFilter->getValue(
                    'long_description',
                    'c91e6a4e-733b-4d77-aefc-129edbf03233',
                    'mobile',
                    'fr_FR'
                ),
            ]
        );
    }

    public function it_returns_an_empty_list_of_enriched_values_to_clean_when_no_values_has_to_be_cleaned(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());

        $this->getEnrichedValuesToClean($valuesToFilter, 'unknown_channel', [])->shouldReturn([]);
        $this->getEnrichedValuesToClean($valuesToFilter, 'mobile', ['en_US', 'fr_FR'])->shouldReturn([]);
    }

    private function getEnrichedValues(): array
    {
        return json_decode('{
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR": {
                    "data": "<p>Ma description enrichie pour le ecommerce</p>\n",
                    "type": "textarea",
                    "locale": "fr_FR",
                    "channel": "ecommerce",
                    "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                },
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR": {
                    "data": "<p>Ma description enrichie pour le mobile</p>\n",
                    "type": "textarea",
                    "locale": "fr_FR",
                    "channel": "mobile",
                    "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                },
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|en_US": {
                    "data": "<p>My enriched description for mobile</p>\n",
                    "type": "textarea",
                    "locale": "en_US",
                    "channel": "mobile",
                    "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                },
                "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911|en_US": {
                    "data": "my_url_slug",
                    "type": "text",
                    "locale": "en_US",
                    "channel": null,
                    "attribute_code": "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911"
                },
                "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911": {
                    "data": "all_scope_all_locale_url_slug",
                    "type": "text",
                    "locale": null,
                    "channel": null,
                    "attribute_code": "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911"
                }
            }',
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
