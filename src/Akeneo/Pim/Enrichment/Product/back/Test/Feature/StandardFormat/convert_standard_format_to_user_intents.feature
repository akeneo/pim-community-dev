@acceptance-back
Feature: Convert standard format to user intents
  In order to enrich my catalog of products
  As a developer
  I need to be able to convert standard format into product user intents

  Background:
    Given the following attributes:
      | code                      | type                   |
      | sku                       | pim_catalog_identifier |
      | name                      | pim_catalog_text       |

  Scenario: Convert successfully a complete standard format in user intents
    When I ask to convert standard format into user intents
    Then there is no exception
    And I obtain all expected user intents

  Scenario: Error when bad family data
    When I ask to convert standard format with an invalid family data
    Then there is an exception with message: Property "family" expects a string as data, "array" given.

  Scenario: Error when bad value formatting
    When I ask to convert standard format into user intents
    Then there is no exception
    And I obtain all expected user intents
