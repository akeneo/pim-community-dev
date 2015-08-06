@javascript
Feature: Unpublish many products at once
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish several products at the same time

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics             |
      | main_color  | Main color  | reference_data_simpleselect | color               |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following published product:
      | sku          | main_color | main_fabric             |
      | red-heels       | Red        | Spandex, Neoprene, Wool |
      | blue-sneakers   | Blue       | Nylon                   |
      | yellow-sneakers | Yellow     | Nylon                   |

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully unpublish several products with reference data
    And I am on the published page
    Then the grid should contain 3 elements
    And I mass-edit products red-heels and blue-sneakers
    When I choose the "Unpublish products" operation
    Then I should see "The 2 selected products will be unpublished"
    When I move on to the next step
    And I wait for the "unpublish" mass-edit job to finish
    And I am on the published page
    Then the grid should contain 1 element
