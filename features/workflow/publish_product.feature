Feature: Publish a product
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully publish a product
    Given the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And I edit the "my-jacket" product
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product my-jacket

  Scenario: Be able to edit the working copy of a publish product I can edit
    And the following published product:
      | sku       | family  | categories | name-en_US |
      | my-jacket | jackets | jackets    | Jacket1    |
    And I am logged in as "Julia"
    And I am on the "my-jacket" published show page
    Then I should see "Edit working copy"

  Scenario: Not be able to edit the working copy of a publish product I can't edit
    And the following published product:
      | sku       | family  | categories | name-en_US |
      | my-tee    | tees    | tshirts    | Tee1       |
    And I am logged in as "Julia"
    And I am on the "my-tee" published show page
    Then I should not see "Edit working copy"
