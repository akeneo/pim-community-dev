@javascript
Feature: Filter products per option
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per option

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label | type         | localizable | scopable | useable as grid filter |
      | color | multiselect  | no          | no       | yes                    |
      | size  | simpleselect | no          | no       | yes                    |
    And the following "color" attribute options: Black and White
    And the following "size" attribute options: S, M and L
    And the following products:
      | sku   | color | size |
      | Shirt |       |      |
      | Sweat |       | M    |
      | Shoes | Black |      |
    And the "Shirt" product has the "color and size" attributes
    And I am logged in as "Mary"

  Scenario: Successfully filter products by a simple option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | value        | result                 |
      | size   | [M]          | Sweat                  |
      | size   | [M],is empty | Sweat, Shoes and Shirt |
      | size   | is empty     | Shirt and Shoes        |

  Scenario: Successfully filter products by a multi option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | value            | result                 |
      | color  | [Black]          | Shoes                  |
      | color  | [Black],is empty | Shoes, Shirt and Sweat |
      | color  | is empty         | Shirt and Sweat        |
