@javascript
Feature: Publish a product
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku             | sole_color | sole_fabric        | family |
      | red-heels       | red        | canvas, conductive | heels  |
    And I am logged in as "Julia"

  Scenario: Successfully publish a product with reference data
    Given I am on the "red-heels" product page
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I am on the published products grid
    Then the grid should contain 1 elements
    And I should see product red-heels
