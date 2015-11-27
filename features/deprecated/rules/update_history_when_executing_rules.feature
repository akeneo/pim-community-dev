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

  @deprecated
  Scenario: Successfully display history after executing a rule
    And the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    converse-sneakers
        actions:
          - type:   set_value
            field:  description
            value:  Chaussures noires classiques
            locale: fr_FR
            scope:  mobile
      """
    When the product rule "set_description" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the "converse-sneakers" product page
    And I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property              | value                        |
      | 2       | Description mobile fr | Chaussures noires classiques |
    And I should see:
    """
    Applied rule "set_description"
    """
  @deprecated
  Scenario: Successfully display history after executing multiple rules
    And the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    converse-sneakers
        actions:
          - type:   set_value
            field:  description
            value:  Chaussures noires classiques
            locale: fr_FR
            scope:  mobile
      set_name:
        priority: 20
        conditions:
          - field:    sku
            operator: =
            value:    converse-sneakers
        actions:
          - type:   set_value
            field:  name
            value:  Chaussures noires
            locale: fr_FR
      """
    When the product rule "set_description" is executed
    And the product rule "set_name" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the "converse-sneakers" product page
    And I open the history
    Then there should be 3 updates
    And I should see history:
      | version | property                 | value                        |
      | 2       | Description mobile fr    | Chaussures noires classiques |
      | 3       | Name fr                  | Chaussures noires            |
    And I should see:
    """
    Applied rule "set_description"
    """
    And I should see:
    """
    Applied rule "set_name"
    """
