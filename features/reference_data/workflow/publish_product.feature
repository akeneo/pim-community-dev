@javascript
Feature: Publish a product
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | property-reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics                      |
      | main_color  | Main color  | reference_data_simpleselect | color                        |
    And I am logged in as "Julia"
    And the following reference data:
      | type   | code     |
      | color  | red      |
      | color  | blue     |
      | fabric | spandex  |
      | fabric | neoprene |
      | fabric | wool     |
    And the following products:
      | sku       | main_color | main_fabric             |
      | red-heels | red        | spandex, neoprene, wool |
    And I am logged in as "Julia"

  Scenario: Successfully publish a product with reference data
    Given I am on the "red-heels" product page
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product red-heels

  #need save working copy to work
  @skip-pef
  Scenario: Successfully edit a published product with reference data
    Given I am on the "red-heels" product page
    When I press the "Publish" button
    And I confirm the publishing
    Then I visit the "Other" group
    And I fill in the following information:
      | Main color  | blue              |
      | Main fabric | spandex, neoprene |
    And I press the "Save working copy" button
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product red-heels
    Then I am on the "red-heels" published show page
    And I should see "[red]"
    And I should see "[spandex]"
    And I should see "[neoprene]"
    And I should see "[wool]"
