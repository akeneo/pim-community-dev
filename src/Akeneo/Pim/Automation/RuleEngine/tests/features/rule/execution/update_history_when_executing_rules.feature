Feature: Update product history when rules are executed
  In order to know what changes to products were made by rules
  As a regular user
  I need to see the history of product updates performed by rules

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku               | family  |
      | converse-sneakers | sneakers |
    And the following product values:
      | product           | attribute   | value                  | locale | scope  |
      | converse-sneakers | name        | Black sneakers         | en_US  |        |
      | converse-sneakers | description | Classic black sneakers | en_US  | mobile |

  @integration-back
  Scenario: Successfully display history after executing a rule
    When the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    converse-sneakers
        actions:
          - type:   set
            field:  description
            value:  Chaussures noires classiques
            locale: fr_FR
            scope:  mobile
      """
    And the history of the product "converse-sneakers" has been built
    And the history of the product "converse-sneakers" has 2 updates
    When the product rule "set_description" is executed
    Then the fr_FR mobile description of "converse-sneakers" should be "Chaussures noires classiques"
    And the history of the product "converse-sneakers" has been built
    And the history of the product "converse-sneakers" has 3 updates
    And a version of the "converse-sneakers" product should be:
      | version | property                 | new_value                    | context                        |
      | 3       | description-fr_FR-mobile | Chaussures noires classiques | Applied rule "set_description" |

  @integration-back
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
          - type:   set
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
          - type:   set
            field:  name
            value:  Chaussures noires
            locale: fr_FR
      """
    And the history of the product "converse-sneakers" has been built
    And the history of the product "converse-sneakers" has 2 updates
    When the product rule "set_description" is executed
    And the product rule "set_name" is executed
    Then the fr_FR mobile description of "converse-sneakers" should be "Chaussures noires classiques"
    And the history of the product "converse-sneakers" has been built
    And the history of the product "converse-sneakers" has 4 updates
    And a version of the "converse-sneakers" product should be:
      | version | property                  | new_value                    | context                        |
      | 3       | description-fr_FR-mobile  | Chaussures noires classiques | Applied rule "set_description" |
      | 4       | name-fr_FR                | Chaussures noires            | Applied rule "set_name"        |
