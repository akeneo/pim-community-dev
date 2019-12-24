@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product
  As a product manager
  I need to have access to a product history

  Scenario: Display product updates
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU | sandals-001 |
    And I press the "Save" button in the popin
    And I wait to be on the "sandals-001" product page
    And the history of the product "sandals-001" has been built
    When I visit the "History" column tab
    Then there should be 1 update
    And I should see history:
      | version | property | value       | date |
      | 1       | SKU      | sandals-001 | now  |

  @jira https://akeneo.atlassian.net/browse/PIM-3628
  Scenario: Update product history when updating product metric
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku   | length | length-unit |
      | boots |     20 | METER       |
    And I am on the "boots" product page
    And I change the "Length" to "30 Centimeter"
    And I save the product
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property    | value      | date |
      | 2       | Length      | 30         | now  |
      | 2       | Length unit | Centimeter | now  |
    When I visit the "Attributes" column tab
    And I change the "Length" to "35 Centimeter"
    And I save the product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property | value | date |
      | 3       | Length   | 35    | now  |
    When I visit the "Attributes" column tab
    And I remove the "Length" attribute
    And I confirm the deletion
    And I save the product
    And the history of the product "boots" has been built
    When I visit the "History" column tab
    Then there should be 4 updates
    And I should see history:
      | version | property    | value | date |
      | 4       | Length      |       | now  |
      | 4       | Length unit |       | now  |
