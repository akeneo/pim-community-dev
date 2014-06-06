@javascript
Feature: Enforce no rights for a category
  In order to be able to prevent some users from viewing some products
  As Peter
  I need to be able to enforce no rights for categories

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Redirect users from the product page to the dashboard when they can't see products in any tree
    Given I am on the products page
    Then I should be on the homepage
    Then I should see "You don't have access to products in any tree, please contact your administrator"
