@javascript
Feature: Unpublish a product
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish a product

  Background:
    Given a "footwear" catalog configuration
    And the following published product:
      | sku             | sole_color | sole_fabric        | family |
      | red-heels       | red        | canvas, conductive | heels  |
      | yellow-heels    | yellow     | conductive         | heels  |
    And I am logged in as "Julia"

  Scenario: Successfully unpublish a product with reference data
    And I am on the "red-heels" published product show page
    When I press the "Unpublish" button
    And I confirm the publishing
    Then I should be on the published index page
    And the grid should contain 1 elements
    And I should see product yellow-heels
    And I should not see product red-heels
