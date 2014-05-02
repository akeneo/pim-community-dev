@javascript
Feature: Filter products per option
  In order to enrich my catalog
  As a user
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
      | sku   |
      | Shirt |
      | Sweat |
      | Shoes |
    And the following product values:
      | product | attribute | value |
      | Shirt   | color     |       |
      | Shirt   | size      |       |
      | Shoes   | color     | Black |
      | Sweat   | size      | M     |
    And I am logged in as "admin"

  Scenario: Successfully filter products by a simple option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | value      | result          |
      | size   | M          | Sweat           |
      | size   | M,is empty | Sweat and Shirt |
