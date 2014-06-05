@javascript
Feature: Publish a product
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Scenario: Successfully publish a product
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I press the "Publish" button
    Then I am on the published products page 
    And the grid should contain 1 elements
    And I should see product my-jacket
