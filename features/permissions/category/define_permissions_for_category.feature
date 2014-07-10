@javascript
Feature: Define permissions for a category
  In order to be able to prevent some users from viewing some products
  As an administrator
  I need to be able to define permissions for categories

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Create category keeps the parent's permissions
    Given I am on the category "2014_collection" node creation page
    When I fill in the following information:
      | Code | newcategory |
    And I save the category
    And I visit the "Permissions" tab
    Then I should see the permission View products with roles Administrator, Manager and User
