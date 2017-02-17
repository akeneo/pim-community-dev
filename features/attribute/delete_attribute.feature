@javascript
Feature: Delete an attribute
  In order to remove an attribute
  As a product manager
  I need to delete a text attribute

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label-en_US | type             | useable_as_grid_filter | localizable | group | code |
      | name        | pim_catalog_text | 1                      | 1           | other | name |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5347
  Scenario: Sucessfully delete and recreate a text attribute used in a product and filter on it
    Given I am on the products page
    When I create a new product
    And I fill in the following information in the popin:
      | SKU | caterpillar_1 |
    And I press the "Save" button in the popin
    Then I wait to be on the "caterpillar_1" product page
    Then I add available attributes name
    And I fill in the following information:
      | name | My caterpillar |
    Then I save the product
    When I am on the products page
    Then the grid should contain 1 elements
    And I should see products caterpillar_1
    Then I should be able to use the following filters:
      | filter | operator | value          | result        |
      | name   | contains | My caterpillar | caterpillar_1 |
    When I am on the attributes page
    Then I click on the "delete" action of the row which contains "name"
    And I confirm the deletion
    And the following attribute:
      | label-en_US | type             | useable_as_grid_filter | localizable | group | code |
      | name        | pim_catalog_text | 1                      | 1           | other | name |
    When I am on the products page
    Then the grid should contain 1 elements
    And I should see products caterpillar_1
    Then I should be able to use the following filters:
      | filter | operator | value          | result |
      | name   | contains | My caterpillar |        |
