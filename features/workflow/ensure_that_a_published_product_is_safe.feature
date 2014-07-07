@javascript
Feature: Ensure that a published product is safe
  In order to forbid to remove part of a product
  As a product manager
  I need to forbid to remove structural part of a published product

  Scenario: Fail to remove a product that have been published
    Given a "clothing" catalog configuration
    And the following published products:
      | sku       |
      | my-jacket |
    And I am logged in as "Julia"
    When I edit the "my-jacket" product
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the products page
    And the grid should contain 1 elements
    And I should see product my-jacket
