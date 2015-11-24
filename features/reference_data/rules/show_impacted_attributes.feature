@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which reference data attributes are affected or not
  As a regular user
  I need to see which reference data attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code            | group | label           | type                        | reference_data_name |
      | new_sole_fabric | info  | New sole fabric | reference_data_multiselect  | fabrics             |
      | new_sole_color  | info  | New sole color  | reference_data_simpleselect | color               |
    And the following "new_sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "new_sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And I am logged in as "Julia"

  Scenario: Successfully create, edit and save a product with reference data
    Given the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product rule definitions:
      """
      set_rule:
        priority: 10
        conditions:
          - field:    sku
            operator: IN
            value:
              - red-heels
          - field:    sole_fabric.code
            operator: IN
            value:
              - PVC
              - Nylon
        actions:
          - type:   set_value
            field:  new_sole_color
            value:  Yellow
          - type:   set_value
            field:  new_sole_fabric
            value:
              - PVC
              - Nylon
              - Neoprene
      """
    When I am on the "red-heels" product page
    And I add available attributes New sole fabric
    And I add available attributes New sole color
    Then I should see that New sole color is a smart
    And I should see that New sole fabric is a smart

