@javascript
Feature: Ensures the appropriate tab is displayed to the user
  In order to ease the contributor's work
  As a product manager
  I should be redirected to my previous product edit form tab

Background:
    Given the "default" catalog configuration
    And the following products:
      | sku          |
      | jacket-white |
      | jacket-black |
    And I am logged in as "Julia"
    And I am on the "Catalog manager" role page
    And I grant rights to Consult the categories of a product
    And I save the role

@jira https://akeneo.atlassian.net/browse/PIM-5395
Scenario: Successfully keeps tabs between products
    Given I am on the "jacket-white" product page
    And I visit the "Categories" tab
    When I click back to grid
    And I click on the "jacket-black" row
    Then I should be on the "Categories" tab

@jira https://akeneo.atlassian.net/browse/PIM-5395
Scenario: Successfully redirects to default tab if the memorized one is not visible anymore
    Given I am on the "jacket-white" product page
    And I visit the "Categories" tab
    And I am on the "Catalog manager" role page
    And I remove rights to Consult the categories of a product
    And I save the role
    When I am on the "jacket-white" product page
    Then I should be on the "Attributes" tab

