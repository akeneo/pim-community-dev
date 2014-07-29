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
    When I press the "Publish" button
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product my-jacket

  Scenario: Successfully unpublish a product
    Given a "clothing" catalog configuration
    And the following published product:
      | sku               | family  | name-en_US      |
      | my-jacket         | jackets | Jackets         |
      | my-leather-jacket | jackets | Leather jackets |
    And I am logged in as "Julia"
    And I am on the "my-jacket" published show page
    When I press the "Unpublish" button
    Then I should be on the published index page
    And the grid should contain 1 elements
    And I should see product my-leather-jacket
    And I should not see product my-jacket

  Scenario: Successfully unpublish a product from the grid
    Given a "clothing" catalog configuration
    And the following published product:
      | sku       | family  | categories | name-en_US      |
      | my-jacket | jackets | jackets    | Jacket1         |
      | my-tee    | tees    | tees       | Tee1            |
    And I am logged in as "Julia"
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should not be able to view the "Unpublish the product" action of the row which contains "my-tee"
    And I should be able to view the "Unpublish the product" action of the row which contains "my-jacket"
    When I click on the "Unpublish the product" action of the row which contains "my-jacket"
    Then the grid should contain 1 elements
    And I should not see product my-jacket
    And I should see product my-tee
