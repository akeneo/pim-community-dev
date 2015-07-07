@javascript
Feature: Update product history when rules are executed
  In order to know what changes to products were made by rules
  As a regular user
  I need to see the history of product updates performed by rules

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"
    And the following product:
      | sku               | family   |
      | converse-sneakers | sneakers |
    And the following product values:
      | product           | attribute   | value                  | locale | scope  |
      | converse-sneakers | name        | Black sneakers         | en_US  |        |
      | converse-sneakers | description | Classic black sneakers | en_US  | mobile |

  Scenario: Successfully display history after executing a rule
    Given the following product rules:
      | code            | priority |
      | set_description | 10       |
    And the following product rule conditions:
      | rule            | field | operator | value             |
      | set_description | sku   | =        | converse-sneakers |
    And the following product rule setter actions:
      | rule            | field       | value                        | locale | scope  |
      | set_description | description | Chaussures noires classiques | fr_FR  | mobile |
    When the product rule "set_description" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the "converse-sneakers" product page
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property                 | value                        |
      | 2       | description-fr_FR-mobile | Chaussures noires classiques |
    And I should see:
    """
    Applied rule "set_description"
    """

  Scenario: Successfully display history after executing multiple rules
    Given the following product rules:
      | code            | priority |
      | set_description | 10       |
      | set_name        | 20       |
    And the following product rule conditions:
      | rule            | field | operator | value             |
      | set_description | sku   | =        | converse-sneakers |
      | set_name        | sku   | =        | converse-sneakers |
    And the following product rule setter actions:
      | rule            | field       | value                        | locale | scope  |
      | set_description | description | Chaussures noires classiques | fr_FR  | mobile |
      | set_name        | name        | Chaussures noires            | fr_FR  |        |
    When the product rule "set_description" is executed
    And the product rule "set_name" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the "converse-sneakers" product page
    And I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property                 | value                        |
      | 2       | description-fr_FR-mobile | Chaussures noires classiques |
      | 3       | name-fr_FR               | Chaussures noires            |
    And I should see:
    """
    Applied rule "set_description"
    """
    And I should see:
    """
    Applied rule "set_name"
    """
