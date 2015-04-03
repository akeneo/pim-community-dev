@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which reference data attributes are affected or not
  As a regular user
  I need to see which reference data attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And I am logged in as "Julia"

  Scenario: Successfully create, edit and save a product
    Given the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product rules:
      | code     | priority |
      | set_rule | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value     |
      | set_rule | sku   | =        | red-heels |
    And the following product rule setter actions:
      | rule     | field       | value                |
      | set_rule | sole_color  | Yellow               |
      | set_rule | sole_fabric | PVC, Nylon, Neoprene |
    When I am on the "red-heels" product page
    And I visit the "Other" group
    Then I should see that Sole color is a smart
    And I should see that Sole fabric is a smart
    And I should see the smart attribute tooltip

