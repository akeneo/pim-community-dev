@javascript
Feature: Filter products by number field
  In order to filter products by number attributes in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully filter products by empty value for number attribute
    Given the following attributes:
      | label       | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | count       | number | no          | no       | yes                    | no               |
      | rate        | number | no          | no       | yes                    | yes              |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value |
      | book    | count     |       |
      | book    | rate      | 9.5   |
      | postit  | count     | 200   |
      | mug     | rate      |       |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value | result |
      | count  | empty | book   |
      | rate   | empty | mug    |
