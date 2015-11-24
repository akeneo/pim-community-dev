@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are linked to a reference data attribute

  Background:
    Given a "footwear" catalog configuration
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following product rule definitions:
      """
      set_sole:
        priority: 10
        conditions:
          - field:    sole_color.code
            operator: IN
            value:
              - Red
              - Green
              - Light green
              - Blue
          - field:    sole_fabric.code
            operator: IN
            value:
              - PVC
              - Nylon
        actions:
          - type:   set_value
            field:  sole_color
            value:  Yellow
          - type:   set_value
            field:  sole_fabric
            value:
              - PVC
              - Nylon
              - Neoprene
      """
    And I am logged in as "Julia"

  Scenario: Successfully show rules of a reference data attribute
    Given I am on the "sole_color" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule     | field            | operator | value                         | locale | scope |
      | set_sole | sole_color.code  | IN       | Red, Green, Light green, Blue |        |       |
      | set_sole | sole_fabric.code | IN       | PVC, Nylon                    |        |       |
    Then I should see the following rule setter actions:
      | rule     | field       | value                | locale | scope |
      | set_sole | sole_color  | Yellow               |        |       |
      | set_sole | sole_fabric | PVC, Nylon, Neoprene |        |       |
