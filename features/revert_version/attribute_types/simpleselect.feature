@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert simpleselect attribute options of a product
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | jean  |
      | family | Pants |
    And I press the "Save" button in the popin
    And I wait to be on the "jean" product page
    And I change the Manufacturer to "Desigual"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "jean" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    And I should see history:
      | version | property     | value    |
      | 2       | Manufacturer | Desigual |
      | 1       | SKU          | jean     |
      | 1       | family       | pants    |
      | 1       | enabled      | 1        |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    Then I should see history:
      | version | property     | value    |
      | 3       | Manufacturer |          |
      | 2       | Manufacturer | Desigual |
      | 1       | SKU          | jean     |
      | 1       | family       | pants    |
      | 1       | enabled      | 1        |
    When I visit the "Attributes" column tab
    Then the product "jean" should have the following values:
      | Manufacturer |  |

  Scenario: Successfully revert a simpleselect attribute
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | marcel |
      | family | Tees   |
    And I press the "Save" button in the popin
    And I wait to be on the "marcel" product page
    And I visit the "Marketing" group
    And I change the "Rating" to "2"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the product
    Then I should not see the text "There are unsaved changes."
    And the history of the product "marcel" has been built
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then the product "marcel" should have the following values:
    | rating | [2] |
