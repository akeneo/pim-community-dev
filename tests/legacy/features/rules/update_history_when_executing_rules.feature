@javascript
Feature: Update product history when rules are executed
  In order to know what changes to products were made by rules
  As a regular user
  I need to see the history of product updates performed by rules

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | converse-sneakers |
      | Family | Sneakers          |
    And I press the "Save" button in the popin
    And I wait to be on the "converse-sneakers" product page
    And I fill in the following information:
      | Name        | Black sneakers         |
      | Description | Classic black sneakers |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."

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
    And the product rule "set_description" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the products grid
    And I am on the "converse-sneakers" product page
    And I visit the "History" column tab
    Then there should be 3 updates
    And I should see history:
      | version | property              | value                        |
      | 3       | Description mobile fr | Chaussures noires classiques |
    And I should see:
    """
    Applied rule "set_description"
    """

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
    When I am on the "converse-sneakers" product page
    And I visit the "History" column tab
    Then there should be 2 updates
    When the product rule "set_description" is executed
    And the history of the product "converse-sneakers" has been built
    And the product rule "set_name" is executed
    And the history of the product "converse-sneakers" has been built
    And I am on the products grid
    And I am on the "converse-sneakers" product page
    And I visit the "History" column tab
    Then there should be 4 updates
    And I should see history:
      | version | property              | value                        |
      | 3       | Description mobile fr | Chaussures noires classiques |
      | 4       | Name fr               | Chaussures noires            |
    And I should see:
    """
    Applied rule "set_description"
    """
    And I should see:
    """
    Applied rule "set_name"
    """
