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

  Scenario: Convert successfully a complete standard format in user intents
    When I ask to convert standard format into user intents
    Then there is no exception
    And I obtain all expected user intents

  @only-ee @reference-entity-feature-enabled @asset-manager-feature-enabled
  Scenario: Convert successfully a complete standard format in user intents with enterprise attributes
    Given the following attributes:
      | code                | type                               |
      | a_record            | akeneo_reference_entity            |
      | a_record_collection | akeneo_reference_entity_collection |
      | an_asset_collection | pim_catalog_asset_collection       |
      | a_table             | pim_catalog_table                  |
    When I ask to convert standard format into user intents with enterprise attributes
    Then there is no exception
    And I obtain all expected user intents

  Scenario: Convert a change parent user intent
    When I ask to convert standard format with a new parent
    Then there is no exception
    And I obtain the expected user intent

  Scenario: Error when bad parent data
    When I ask to convert standard format with an invalid parent data
    Then there is an exception with message: Property "parent" expects a string as data, "array" given.

  Scenario: Error when bad family data
    When I ask to convert standard format with an invalid family data
    Then there is an exception with message: Property "family" expects a string as data, "array" given.

  Scenario: Error when bad categories data
    When I ask to convert standard format with an invalid categories data
    Then there is an exception with message: Property "categories" expects an array as data, "string" given.

  Scenario: Error when bad enabled data
    When I ask to convert standard format with an invalid enabled data
    Then there is an exception with message: Property "enabled" expects a boolean as data, "NULL" given.

  Scenario: Error when bad associations data
    When I ask to convert standard format with an invalid associations data
    Then there is an exception with message: Property "associations" expects an array as data, "string" given.

  Scenario: Error when bad groups data
    When I ask to convert standard format with an invalid groups data
    Then there is an exception with message: Property "groups" expects an array as data, "string" given.

  Scenario: Error when bad boolean attribute value
    When I ask to convert standard format with an invalid boolean attribute value
    Then there is an exception with message: Property "a_boolean" expects a boolean as data, "string" given.

  Scenario: Error when bad text attribute value
    When I ask to convert standard format with an invalid text attribute value
    Then there is an exception with message: Property "name" expects a string as data, "array" given.

  Scenario: Error when bad textarea attribute value
    When I ask to convert standard format with an invalid textarea attribute value
    Then there is an exception with message: Property "a_textarea" expects a string as data, "array" given.

  Scenario: Error when bad date attribute value
    When I ask to convert standard format with an invalid date attribute value
    Then there is an exception with message: Property "a_date" expects a string with the format "yyyy-mm-dd" as data, "september 10th" given.

  Scenario: Error when bad file attribute value
    When I ask to convert standard format with an invalid file attribute value
    Then there is an exception with message: Property "a_file" expects a string as data, "array" given.

  Scenario: Error when bad image attribute value
    When I ask to convert standard format with an invalid image attribute value
    Then there is an exception with message: Property "an_image" expects a string as data, "array" given.

  Scenario: Error when bad metric attribute value
    When I ask to convert standard format with an invalid measurement attribute value
    Then there is an exception with message: Property "a_metric" expects an array as data, "integer" given.

  Scenario: Error when bad metric attribute value
    When I ask to convert standard format with a measurement without unit
    Then there is an exception with message: Property "a_metric" expects an array with the key "unit".

  Scenario: Error when bad metric attribute value
    When I ask to convert standard format with a measurement with empty unit
    Then there is an exception with message: Property "a_metric" expects a string as data, "array" given.

  Scenario: Error when bad metric attribute value
    When I ask to convert standard format with a measurement with null unit
    Then there is an exception with message: Property "a_metric" expects a string as data, "NULL" given.

  Scenario: Error when bad multiselect attribute value
    When I ask to convert standard format with an invalid simpleselect attribute value
    Then there is an exception with message: Property "a_simpleselect" expects a string as data, "array" given.

  Scenario: Error when bad multiselect attribute value
    When I ask to convert standard format with an invalid multiselect attribute value
    Then there is an exception with message: Property "a_multiselect" expects an array as data, "string" given.

  Scenario: Error when bad price attribute value
    When I ask to convert standard format with an invalid price attribute value
    Then there is an exception with message: Property "a_price" expects an array as data, "integer" given.

  @only-ge-ee
  Scenario: Error when bad table attribute value
    Given the following attributes:
      | code                | type              |
      | a_table             | pim_catalog_table |
    When I ask to convert standard format with an invalid table attribute value
    Then there is an exception with message: Property "a_table" expects an array as data, "string" given.

  @only-ee @reference-entity-feature-enabled
  Scenario: Error when bad simple reference entity attribute value
    Given the following attributes:
      | code                | type                    |
      | a_record            | akeneo_reference_entity |
    When I ask to convert standard format with an invalid simple reference entity attribute value
    Then there is an exception with message: Property "a_record" expects a string as data, "array" given.

  @only-ee @reference-entity-feature-enabled
  Scenario: Error when bad multi reference entity attribute value
    Given the following attributes:
      | code                | type                               |
      | a_record_collection | akeneo_reference_entity_collection |
    When I ask to convert standard format with an invalid multi reference entity attribute value
    Then there is an exception with message: Property "a_record_collection" expects an array as data, "string" given.

  @only-ee  @asset-manager-feature-enabled
  Scenario: Error when bad asset collection attribute value
    Given the following attributes:
      | code                | type                         |
      | an_asset_collection | pim_catalog_asset_collection |
    When I ask to convert standard format with an invalid asset collection attribute value
    Then there is an exception with message: Property "an_asset_collection" expects an array as data, "string" given.
