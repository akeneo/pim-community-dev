@javascript
Feature: Publish a product
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics             |
      | main_color  | Main color  | reference_data_simpleselect | color               |
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

  Scenario: Successfully edit a published product with reference data
    Given I am on the "red-heels" product page
    When I press the "Publish" button
    And I confirm the publishing
    Then I visit the "Other" group
    And I fill in the following information:
      | Main color  | blue              |
      | Main fabric | spandex, neoprene |
    And I save the product
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product red-heels
    Then I am on the "red-heels" published show page
    And I visit the "Other" group
    And I should see the text "[red]"
    And I should see the text "[spandex]"
    And I should see the text "[neoprene]"
    And I should see the text "[wool]"
