@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which reference data attributes are affected or not
  As a regular user
  I need to see which reference data attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code            | group | label-en_US     | type                            | reference_data_name |
      | new_sole_fabric | info  | New sole fabric | pim_reference_data_multiselect  | fabrics             |
      | new_sole_color  | info  | New sole color  | pim_reference_data_simpleselect | color               |
    And the following family:
      | code       | attributes                                                     |
      | high_heels | sku,name,sole_color,new_sole_color,sole_fabric,new_sole_fabric |
    And I am logged in as "Julia"

  Scenario: Successfully create, edit and save a product with reference data
    Given the following products:
      | sku       | family     |
      | red-heels | high_heels |
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
              - nylon
        actions:
          - type:   set
            field:  new_sole_color
            value:  yellow
          - type:   set
            field:  new_sole_fabric
            value:
              - neoprene
      """
    When I am on the "red-heels" product page
    Then I should see that New sole color is a smart attribute
    And I should see that New sole fabric is a smart attribute

