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
    Then the row "set_sole" should contain the texts:
      | column    | value                                               |
      | Condition | If sole_color.code in Red, Green, Light green, Blue |
      | Condition | If sole_fabric.code in PVC, Nylon                   |
      | Action    | Then Yellow is set into sole_color                  |
      | Action    | Then PVC, Nylon, Neoprene is set into sole_fabric   |
