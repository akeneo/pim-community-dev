@javascript
Feature: Unpublish a product
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish a product

  Scenario: Successfully unpublish a product
    Given a "clothing" catalog configuration
    And the following published product:
    | sku               | family  | name-en_US      |
    | my-jacket         | jackets | Jackets         |
    | my-leather-jacket | jackets | Leather jackets |
    And I am logged in as "Julia"
    And I am on the "my-jacket" published product show page
    When I press the "Unpublish" button
    And I confirm the publishing
    Then I should be on the published index page
    And the grid should contain 1 elements
    And I should see product my-leather-jacket
    And I should not see product my-jacket

  Scenario: Successfully unpublish a product from the grid
    Given a "clothing" catalog configuration
    And the following published product:
    | sku       | family  | categories | name-en_US |
    | my-jacket | jackets | jackets    | Jacket1    |
    | my-tee    | tees    | tees       | Tee1       |
    And I am logged in as "Julia"
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should not be able to view the "Unpublish the product" action of the row which contains "my-tee"
    And I should be able to view the "Unpublish the product" action of the row which contains "my-jacket"
    When I click on the "Unpublish the product" action of the row which contains "my-jacket"
    And I refresh current page
    Then the grid should contain 1 elements
    And I should not see product my-jacket
    And I should see product my-tee

  Scenario: Not being able to unpublish a product I am not owner
    Given a "clothing" catalog configuration
    And the following published product:
      | sku    | family | categories | name-en_US |
      | my-tee | tees   | tees       | Tee1       |
    And I am logged in as "Julia"
    When I am on the "my-tee" published product show page
    Then I should not see "Unpublish"

  Scenario: Being able to unpublish a product from the grid and view when I am the owner
    Given a "clothing" catalog configuration
    And the following published product:
      | sku       | family  | categories | name-en_US | number_in_stock-mobile | number_in_stock-tablet |
      | my-jacket | jackets | jackets    | Jacket1    | 20                     | 30                     |
    And I am logged in as "Julia"
    And I am on the published products grid
    Then the grid should contain 1 element
    And I should be able to view the "Unpublish the product" action of the row which contains "my-jacket"
    When I am on the "my-jacket" published product show page
    Then I should see the text "Unpublish"

  Scenario: Not being able to unpublish a product from the grid and view when I am not the owner
    Given a "clothing" catalog configuration
    And the following published product:
      | sku       | family  | categories | name-en_US | number_in_stock-mobile | number_in_stock-tablet |
      | my-jacket | jackets | tees       | Jacket1    | 20                     | 30                     |
    And I am logged in as "Julia"
    And I am on the published products grid
    Then the grid should contain 1 element
    And I should not be able to view the "Unpublish the product" action of the row which contains "my-jacket"
    When I am on the "my-jacket" published product show page
    Then I should not see "Unpublish"
