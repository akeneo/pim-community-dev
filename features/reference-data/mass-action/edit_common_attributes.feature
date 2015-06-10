@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same reference data
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code           | attributes                                             |
      | platform_shoes | sku, name, description, color, heel_color, sole_fabric |
    And the following attributes:
      | code        | label       | type   | metric family | default metric unit | families       |
      | heel_height | Heel height | metric | Length        | CENTIMETER          | platform_shoes |
    And the following "heel_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following products:
      | sku            | family         |
      | platform_shoes | platform_shoes |
      | heels          | heels          |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully update single-valued reference data at once
    Given I mass-edit products heels and platform_shoes
    And I choose the "Edit common attributes" operation
    And I display the Heel color attribute
    And I change the "Heel color" to "Light green"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "heels" should have the following values:
      | heel_color | Light green |
    And the product "platform_shoes" should have the following values:
      | heel_color | Light green |

  Scenario: Successfully update multi-valued reference data at once
    Given I mass-edit products heels and platform_shoes
    And I choose the "Edit common attributes" operation
    And I display the Sole fabric attribute
    And I change the "Sole fabric" to "Wool, Kevlar, Jute"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "heels" should have the following values:
      | sole_fabric | Wool, Kevlar, Jute |
    Then the product "platform_shoes" should have the following values:
      | sole_fabric | Wool, Kevlar, Jute |
