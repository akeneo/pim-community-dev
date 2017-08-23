@javascript
Feature: Delete an attribute
  In order to remove an attribute
  As a product manager
  I need to delete a text attribute

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5347
  Scenario: Successfully delete and recreate a text attribute used in a product and filter on it
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | caterpillar_1 |
      | family | Boots         |
    And I press the "Save" button in the popin
    And I wait to be on the "caterpillar_1" product page
    And I visit the "Product information" group
    And I change the "Name" to "My caterpillar"
    And I save the product
    When I am on the products grid
    Then the grid should contain 1 elements
    And I should see products caterpillar_1
    And I should be able to use the following filters:
      | filter | operator | value          | result        |
      | name   | contains | My caterpillar | caterpillar_1 |
    When I am on the attributes page
    And I click on the "delete" action of the row which contains "Name"
    And I confirm the deletion
    And the following attribute:
      | code | label-en_US | type             | useable_as_grid_filter | localizable | group |
      | name | name        | pim_catalog_text | 1                      | 1           | other |
    And I am on the products grid
    Then the grid should contain 1 elements
    And I should see products caterpillar_1
    And I should be able to use the following filters:
      | filter | operator | value          | result |
      | name   | contains | My caterpillar |        |
