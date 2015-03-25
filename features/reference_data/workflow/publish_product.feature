@javascript
Feature: Publish a product with reference data
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

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
      | sku         | main_color | main_fabric             |
      | red-heels   | Red        | Spandex, Neoprene, Wool |
    And I am logged in as "Julia"

  Scenario: Successfully publish a product
    Given I am on the "red-heels" product page
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product red-heels

  Scenario: Successfully edit a published product
    Given I am on the "red-heels" product page
    When I press the "Publish" button
    And I confirm the publishing
    Then I visit the "Other" group
    And I fill in the following information:
      | Main color  | Blue              |
      | Main fabric | Spandex, Neoprene |
    And I press the "Save working copy" button
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product red-heels
    Then I am on the "red-heels" published show page
    And I should see "Red"
    And I should see "Spandex"
    And I should see "Neoprene"
    And I should see "Wool"
