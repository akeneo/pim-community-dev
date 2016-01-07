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
    Given I am on the "jean" product page
    Given I add a new option to the "Weather conditions" attribute:
    | Code | very_wet      |
    | en   | Extremely wet |
    And I save the product
    And the history of the product "jean" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then I should see a flash message "Product successfully reverted"

  Scenario: Successfully revert a pim_catalog_multiselect attribute
    Given the following product:
    | sku     | family | weather_conditions |
    | t-shirt | tees   | Dry, Cold          |
    | marcel  | tees   |                    |
    Given I am on the "t-shirt" product page
    And I change the "Weather conditions" to ""
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | weather_conditions | [dry], [cold] |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    Then I add available attributes Weather conditions
    And I change the "Weather conditions" to "Hot, Wet"
    And I save the product
    And the history of the product "marcel" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | weather_conditions |  |
