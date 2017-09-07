@javascript
Feature: Publish many products at once
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                            | reference_data_name | group |
      | main_fabric | Main fabric | pim_reference_data_multiselect  | fabrics             | other |
      | main_color  | Main color  | pim_reference_data_simpleselect | color               | other |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following products:
      | sku             | main_color | main_fabric             |
      | red-heels       | Red        | Spandex, Neoprene, Wool |
      | blue-sneakers   | Blue       | Nylon                   |
      | yellow-sneakers | Yellow     | Nylon                   |

  Scenario: Successfully publish several products with reference data
    Given I am on the published products grid
    Then the grid should contain 0 elements
    When I am on the products grid
    And I select rows red-heels, blue-sneakers and yellow-sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Publish products" operation
    And I should see the text "The 3 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    When I am on the published products grid
    Then the grid should contain 3 elements
