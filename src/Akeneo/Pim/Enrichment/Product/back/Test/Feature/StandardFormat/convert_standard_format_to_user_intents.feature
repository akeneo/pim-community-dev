@acceptance-back
Feature: Convert standard format to user intents
  In order to enrich my catalog of products
  As a developer
  I need to be able to convert standard format into product user intents

  Background:
    Given the following attributes:
      | code                | type                               |
      | sku                 | pim_catalog_identifier             |
      | ean                 | pim_catalog_identifier             |
      | name                | pim_catalog_text                   |
      | a_boolean           | pim_catalog_boolean                |
      | a_date              | pim_catalog_date                   |
      | a_file              | pim_catalog_file                   |
      | an_image            | pim_catalog_image                  |
      | a_metric            | pim_catalog_metric                 |
      | a_number            | pim_catalog_number                 |
      | a_multiselect       | pim_catalog_multiselect            |
      | a_simpleselect      | pim_catalog_simpleselect           |
      | a_price             | pim_catalog_price_collection       |
      | a_textarea          | pim_catalog_textarea               |
      | a_record            | akeneo_reference_entity            |
      | a_record_collection | akeneo_reference_entity_collection |
      | an_asset_collection | pim_catalog_asset_collection       |
      | a_table             | pim_catalog_table                  |

  Scenario: Convert successfully a complete standard format in user intents
    When I ask to convert standard format into user intents
    Then there is no exception
    And I obtain all expected user intents

  Scenario: Error when bad family data
    When I ask to convert standard format with an invalid family data
    Then there is an exception with message: Property "family" expects a string as data, "array" given.
