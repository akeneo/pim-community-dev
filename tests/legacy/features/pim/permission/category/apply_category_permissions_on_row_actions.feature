@javascript
Feature: Apply defined category permissions on product grid row actions
  In order to know when I have the rights to do some actions
  As Julia
  I want to see product grid row actions only when I have the rights to execute them

  Scenario: Display the product classification action only if the user owns the product
    Given a "clothing" catalog configuration
    And the following products:
      | sku             | categories |
      | ownedProduct    | jackets    |
      | editableProduct | tees       |
    And I am logged in as "Julia"
    When I am on the products grid
    Then the grid should contain 2 elements
    And I should see products ownedProduct and editableProduct
    And I should be able to view the "Classify the product" action of the row which contains "ownedProduct"
    But I should not be able to view the "Classify the product" action of the row which contains "editableProduct"
