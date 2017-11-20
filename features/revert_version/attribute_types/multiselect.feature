@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert multiselect attribute options of a product
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | jean  |
      | family | Pants |
    And I press the "Save" button in the popin
    And I wait to be on the "jean" product page
    And I add a new option to the "Weather conditions" attribute:
    | Code | very_wet      |
    | en   | Extremely wet |
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "jean" has been built
    And I visit the "History" column tab
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
    When I visit the "Attributes" column tab
    Then the product "jean" should have the following values:
      | weather_conditions |  |

  Scenario: Successfully revert a pim_catalog_multiselect attribute
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | marcel  |
      | family | Jackets |
    And I press the "Save" button in the popin
    And I wait to be on the "marcel" product page
    And I visit the "Attributes" column tab
    And I change the "Weather conditions" to "Hot, Wet"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "marcel" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    And I should see history:
      | version | property           | value   |
      | 2       | Weather conditions | wet,hot |
      | 1       | SKU                | marcel  |
      | 1       | family             | jackets |
      | 1       | enabled            | 1       |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    And I should see history:
      | version | property           | value   |
      | 3       | Weather conditions |         |
      | 2       | Weather conditions | wet,hot |
      | 1       | SKU                | marcel  |
      | 1       | family             | jackets |
      | 1       | enabled            | 1       |
    And the product "marcel" should have the following values:
    | weather_conditions |  |
