@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following group type:
      | code    | label-en_US |
      | related | Related     |
    And the following product groups:
      | code   | label-en_US | type    |
      | POSTIT | Postit      | X_SELL  |
      | EMPTY  | Empty       | X_SELL  |
      | MUG    | Mug         | related |
    And the following products:
      | sku    | groups   |
      | BOOK   |          |
      | MUG-1  | MUG      |
      | MUG-2  | MUG      |
      | POSTIT | POSTIT   |
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully filter by group
    Given I am on the products grid
    When I filter by "groups" with operator "" and value "Mug"
    Then the grid should contain 2 elements
    And I should see products MUG-1 and MUG-2
    And I should not see products BOOK and POSTIT
