@javascript
Feature: Unpublish a product
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish a product

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                            | reference_data_name | group |
      | main_fabric | Main fabric | pim_reference_data_multiselect  | fabrics             | other |
      | main_color  | Main color  | pim_reference_data_simpleselect | color               | other |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following published product:
      | sku          | main_color | main_fabric             |
      | red-heels    | Red        | Spandex, Neoprene, Wool |
      | yellow-heels | Yellow     | Wool                    |

  Scenario: Successfully unpublish a product with reference data
    And I am on the "red-heels" published product show page
    When I press the "Unpublish" button
    And I confirm the publishing
    Then I should be on the published index page
    And the grid should contain 1 elements
    And I should see product yellow-heels
    And I should not see product red-heels
