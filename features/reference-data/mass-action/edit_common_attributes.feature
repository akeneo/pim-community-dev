@javascript
Feature: Mass edit common attributes for reference data
  In order to update many products with the same reference data
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | heel_height | Heel height | pim_catalog_metric | Length        | CENTIMETER          | other | 0                | 0                |
    And the following family:
      | code           | attributes                                                    |
      | platform_shoes | sku,name,description,color,heel_color,sole_fabric,heel_height |
    And the following "heel_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following products:
      | sku            | family         |
      | platform_shoes | platform_shoes |
      | heels          | heels          |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully update single-valued reference data at once
    Given I select rows heels and platform_shoes
    And I press "Bulk actions" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Heel color attribute
    And I change the "Heel color" to "Light green"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product "heels" should have the following values:
      | heel_color | Light green |
    And the product "platform_shoes" should have the following values:
      | heel_color | Light green |

  Scenario: Successfully update multi-valued reference data at once
    Given I select rows heels and platform_shoes
    And I press "Bulk actions" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Sole fabric attribute
    And I change the "Sole fabric" to "Wool, Kevlar, Jute"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product "heels" should have the following values:
      | sole_fabric | Jute, Kevlar, Wool|
    Then the product "platform_shoes" should have the following values:
      | sole_fabric | Jute, Kevlar, Wool|
