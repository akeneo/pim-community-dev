@javascript
Feature: Filter products by number field
  In order to filter products by number attributes in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully filter products by empty value for number attributes
    Given the following attributes:
      | label | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | count | number | no          | no       | yes                    | no               |
      | rate  | number | no          | no       | yes                    | yes              |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product| attribute | value |
      | book   | count     |       |
      | book   | rate      | 9.5   |
      | postit | count     | 200   |
      | mug    | rate      |       |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value | result |
      | count  | empty | book   |
      | rate   | empty | mug    |

  Scenario: Successfully filter products by empty value for localized number attribute
    Given the following attributes:
      | label | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | pages | number | yes         | no       | yes                    | no               |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value | locale |
      | book    | pages     | 250   | en_US  |
      | book    | pages     | 250   | fr_FR  |
      | postit  | pages     |       | en_US  |
      | postit  | pages     | 5     | fr_FR  |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value | result |
      | pages  | empty | postit |
      
  Scenario: Successfully filter products by empty value for scopable number attribute
    Given the following attributes:
      | label | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | pages | number | no          | yes      | yes                    | no               |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value | scope     |
      | book    | pages     | 250   | ecommerce |
      | book    | pages     | 250   | mobile    |
      | postit  | pages     |       | ecommerce |
      | postit  | pages     | 5     | mobile    |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value | result |
      | pages  | empty | postit |

  Scenario: Successfully filter products by empty value for localizable and scopable number attribute
    Given the following attributes:
      | label | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | pages | number | yes         | yes      | yes                    | no               |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value | scope     | locale |
      | book    | pages     |       | ecommerce | en_US  |
      | book    | pages     | 250   | ecommerce | fr_FR  |
      | book    | pages     | 250   | mobile    | en_US  |
      | book    | pages     | 250   | mobile    | fr_FR  |
      | postit  | pages     | 10    | ecommerce | en_US  |
      | postit  | pages     | 5     | ecommerce | fr_FR  |
      | postit  | pages     | 5     | mobile    | en_US  |
      | postit  | pages     | 5     | mobile    | fr_FR  |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value | result |
      | pages  | empty | book   |
      