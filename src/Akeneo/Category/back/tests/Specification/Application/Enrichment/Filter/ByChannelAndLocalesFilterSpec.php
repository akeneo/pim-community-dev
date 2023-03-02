<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Enrichment\Filter;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ByChannelAndLocalesFilterSpec extends ObjectBehavior
{
    public function it_returns_the_list_of_enriched_values_to_clean_while_cleaning_channel(): void
    {
        $valuesToFilter = $this->getEnrichedValues();

        $this->getEnrichedValueCompositeKeysToClean($valuesToFilter, 'mobile', [])->shouldReturn(
            [
                'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR',
                'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|en_US',
            ]
        );
    }

    public function it_does_nothing_when_deleted_channel_code_is_null(): void
    {
        $valuesToFilter = $this->getEnrichedValues();

        $this->getEnrichedValueCompositeKeysToClean($valuesToFilter, '', [])->shouldReturn([]);
    }

    public function it_returns_the_list_of_enriched_values_to_clean_while_cleaning_locales(): void
    {
        $valuesToFilter = $this->getEnrichedValues();

        $this->getEnrichedValueCompositeKeysToClean($valuesToFilter, 'mobile', ['en_US'])->shouldReturn(
            [
                'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR',
            ]
        );
    }

    public function it_returns_an_empty_list_of_enriched_values_to_clean_when_no_values_has_to_be_cleaned(): void
    {
        $valuesToFilter = $this->getEnrichedValues();

        $this->getEnrichedValueCompositeKeysToClean($valuesToFilter, 'unknown_channel', [])->shouldReturn([]);
        $this->getEnrichedValueCompositeKeysToClean($valuesToFilter, 'mobile', ['en_US', 'fr_FR'])->shouldReturn([]);
    }

    private function getEnrichedValues(): array
    {
        return json_decode('{
                "attribute_codes": [
                    "seo_keywords|0175e701-fde6-4215-91ea-8e2992a7866f",
                    "seo_h1_main_heading_tag|534dfa6b-f4ad-40a8-ad73-65d7abd1c6fe",
                    "seo_meta_description|c7d45ce5-6a73-40ac-b417-e17b44aa4f54",
                    "seo_meta_title|bfef2643-4b82-49d9-bf9e-946d4fcd6404",
                    "image_alt_text_3|90245352-d7e5-4880-a143-bd2436864999",
                    "image_3|d0f0d583-b311-4185-b5f1-8bc625a99a9e",
                    "image_alt_text_2|0d775f10-4a2e-43be-84ab-5870480112a7",
                    "image_2|50981206-6132-4227-9c98-bedb7d109f54",
                    "image_alt_text_1|c43edcdf-d18b-43ee-bac7-658a6bc7cb52",
                    "image_1|69d18c72-0cb5-4561-8c26-a4dab02c10d9",
                    "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911",
                    "short_description|19915d40-00cd-422d-aa61-c27923966750",
                    "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                ],
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
