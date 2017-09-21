@javascript
Feature: Unpublish many products at once
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish several products at the same time

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
      | sku             | main_color | main_fabric             |
      | red-heels       | Red        | Spandex, Neoprene, Wool |
      | blue-sneakers   | Blue       | Nylon                   |
      | yellow-sneakers | Yellow     | Nylon                   |

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully unpublish several products with reference data
    And I am on the published products grid
    Then the grid should contain 3 elements
    And I select rows red-heels and blue-sneakers
    And I press the "Bulk actions" button
    When I choose the "Unpublish" operation
    Then I should see the text "The 2 selected products will be unpublished"
    When I confirm mass edit
    And I wait for the "unpublish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 1 element

  @jira https://akeneo.atlassian.net/browse/PIM-4895
  Scenario: Redirection to unpublished product after mass edit
    When I am on the published products grid
    And I select rows red-heels and blue-sneakers
    And I press the "Bulk actions" button
    When I choose the "Unpublish" operation
    Then I confirm mass edit
    Then I should be redirected on the published products page
