@javascript
Feature: Ensure that a published product is safe
  In order to forbid to remove part of a product
  As a product manager
  I need to forbid to remove structural part of a published product

  Background:
    Given a "clothing" catalog configuration
    And the following published products:
      | sku       | categories | family  | groups          |
      | my-jacket | jackets    | jackets | similar_jackets |
    And I am logged in as "Julia"

  Scenario: Fail to remove a product that has been published
    Given I am on the "my-jacket" product page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the products page
    And the grid should contain 1 element
    And I should see product my-jacket

  Scenario: Fail to remove a category that is linked to a published product
    Given I am on the "jackets" category page
    And I press the "Delete" button
    And I confirm the removal
    And I should see the "Jackets" category under the "Summer collection" category

  Scenario: Fail to remove a family that is linked to a published product
    Given I am on the "jackets" family page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the families page
    And the grid should contain 3 element
    And I should see family jackets
