@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert multiselect attribute options of a product
    Given the following product:
    | sku  | family |
    | jean | pants  |
    And I am on the "jean" product page
    And I add a new option to the "Weather conditions" attribute:
    | Code | very_wet      |
    | en   | Extremely wet |
    And I save the product
    And the history of the product "jean" has been built
    And I open the history
    Then I should see 2 versions in the history
    And I should see history:
      | version | property           | value    |
      | 2       | Weather conditions | very_wet |
      | 1       | SKU                | jean     |
      | 1       | family             | pants    |
      | 1       | enabled            | 1        |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    And I should see history:
      | version | property           | value    |
      | 3       | Weather conditions |          |
      | 2       | Weather conditions | very_wet |
      | 1       | SKU                | jean     |
      | 1       | family             | pants    |
      | 1       | enabled            | 1        |
    When I visit the "Attribute" tab
    Then the product "jean" should have the following values:
      | weather_conditions | |

  Scenario: Successfully revert a pim_catalog_multiselect attribute
    Given the following product:
    | sku     | family | weather_conditions |
    | t-shirt | tees   | Dry, Cold          |
    | marcel  | tees   |                    |
    And I am on the "t-shirt" product page
    When I change the "Weather conditions" to ""
    And I save the product
    And the history of the product "t-shirt" has been built
    And I open the history
    Then I should see 2 versions in the history
    And I should see history:
      | version | property           | value    |
      | 2       | Weather conditions |          |
      | 1       | Weather conditions | dry,cold |
      | 1       | SKU                | t-shirt  |
      | 1       | family             | tees     |
      | 1       | enabled            | 1        |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    And I should see history:
      | version | property           | value    |
      | 3       | Weather conditions | dry,cold |
      | 2       | Weather conditions |          |
      | 1       | Weather conditions | dry,cold |
      | 1       | SKU                | t-shirt  |
      | 1       | family             | tees     |
      | 1       | enabled            | 1        |
    Then the product "t-shirt" should have the following values:
    | weather_conditions | [dry], [cold] |
    When I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I add available attributes Weather conditions
    And I change the "Weather conditions" to "Hot, Wet"
    And I save the product
    And the history of the product "marcel" has been built
    And I open the history
    Then I should see 2 versions in the history
    And I should see history:
      | version | property           | value    |
      | 2       | Weather conditions | wet,hot  |
      | 1       | SKU                | marcel   |
      | 1       | family             | tees     |
      | 1       | enabled            | 1        |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    And I should see history:
      | version | property           | value    |
      | 3       | Weather conditions |          |
      | 2       | Weather conditions | wet,hot  |
      | 1       | SKU                | marcel   |
      | 1       | family             | tees     |
      | 1       | enabled            | 1        |
    Then the product "marcel" should have the following values:
    | weather_conditions |  |
