@javascript
Feature: Publish many products at once
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | property-reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics                        |
      | main_color  | Main color  | reference_data_simpleselect | color                          |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following products:
      | sku             | main_color | main_fabric             |
      | red-heels       | Red        | Spandex, Neoprene, Wool |
      | blue-sneakers   | Blue       | Nylon                   |
      | yellow-sneakers | Yellow     | Nylon                   |
    And I am logged in as "Julia"

  Scenario: Successfully publish several products with reference data
    Given I am on the published index page
    Then the grid should contain 0 elements
    When I am on the products page
    And I mass-edit products red-heels, blue-sneakers and yellow-sneakers
    And I choose the "Publish products" operation
    And I should see "The 3 selected products will be published"
    And I move on to the next step
    And I wait for the "publish" mass-edit job to finish
    When I am on the published index page
    Then the grid should contain 3 elements
