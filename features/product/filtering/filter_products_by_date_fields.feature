@javascript
Feature: Filter products by date field
  In order to filter products by date attributes in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully filter products by empty value for date attribute
    Given the following attributes:
      | label   | type | localizable | scopable | useable as grid filter |
      | release | date | no          | no       | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product| attribute | value      |
      | book   | release   |            |
      | postit | release   | 2014-05-01 |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | value | result |
      | release | empty | book   |

  Scenario: Successfully filter products by empty value for localized date attribute
    Given the following attributes:
      | label   | type | localizable | scopable | useable as grid filter |
      | release | date | yes         | no       | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value      | locale |
      | book    | release   | 2014-04-28 | en_US  |
      | book    | release   | 2014-04-28 | fr_FR  |
      | postit  | release   |            | en_US  |
      | postit  | release   | 2014-04-30 | fr_FR  |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | value | result |
      | release | empty | postit |

  Scenario: Successfully filter products by empty value for scopable number attribute
    Given the following attributes:
      | label   | type   | localizable | scopable | useable as grid filter | decimals allowed |
      | release | number | no          | yes      | yes                    | no               |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product | attribute | value      | scope     |
      | book    | release   | 2014-04-30 | ecommerce |
      | book    | release   | 2014-05-05 | mobile    |
      | postit  | release   |            | ecommerce |
      | postit  | release   | 2014-05-30 | mobile    |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | value | result |
      | release | empty | postit |

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
