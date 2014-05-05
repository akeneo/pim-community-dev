@javascript
Feature: Filter products by text field
  In order to filter products by text attributes in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully filter products by empty value for text and textarea attributes
    Given the following attributes:
      | label       | type     | localizable | scopable | useable as grid filter |
      | name        | text     | no          | no       | yes                    |
      | description | textarea | no          | no       | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute   | value            |
      | postit  | name        | MyPostit         |
      | postit  | description |                  |
      | book    | name        |                  |
      | mug     | description | MyMugDescription |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter      | value | result          |
      | name        | empty | book and mug    |
      | description | empty | postit and name |

  Scenario: Successfully filter products by empty value for localizable text attribute
    Given the following attributes:
      | label | type | localizable | scopable | useable as grid filter |
      | name  | text | yes         | no       | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value     | locale |
      | postit  | name      | MyPostit  | en_US  |
      | postit  | name      | MonPostit | fr_FR  |
      | book    | name      |           | en_US  |
      | book    | name      | MonLivre  | fr_FR  |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result |
      | name   | empty | book   |

  Scenario: Successfully filter products by empty value for scopable text attribute
    Given the following attributes:
      | label | type | localizable | scopable | useable as grid filter |
      | name  | text | no          | yes      | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value    | scope     |
      | postit  | name      | MyPostit | ecommerce |
      | postit  | name      | MyPostit | mobile    |
      | book    | name      |          | ecommerce |
      | book    | name      | MyBook   | mobile    |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result |
      | name   | empty | book   |

  Scenario: Successfully filter products by empty value for scopable and localizable text attribute
    Given the following attributes:
      | label | type | localizable | scopable | useable as grid filter |
      | name  | text | yes         | yes      | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value     | scope     | locale |
      | postit  | name      | MyPostit  | ecommerce | en_US  |
      | postit  | name      | MonPostit | ecommerce | fr_FR  |
      | postit  | name      | MyPostit  | mobile    | en_US  |
      | postit  | name      | MonPostit | mobile    | fr_FR  |
      | book    | name      |           | ecommerce | en_US  |
      | book    | name      | MonLivre  | ecommerce | fr_FR  |
      | book    | name      | MyBook    | mobile    | en_US  |
      | book    | name      | MonLivre  | mobile    | fr_FR  |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result |
      | name   | empty | book   |
