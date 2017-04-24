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
    When I am logged in as "Julia"
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I grant rights to resource Consult the categories of a product
    And I save the role
    Then I should not see the text "There are unsaved changes."

  @jira https://akeneo.atlassian.net/browse/PIM-5395
  Scenario: Successfully keeps tabs between products
    Given I am on the "jacket-white" product page
    And I visit the "Categories" column tab
    And I am on the products page
    And I am on the "jacket-white" product page
    Then I should be on the "Categories" column tab

  @jira https://akeneo.atlassian.net/browse/PIM-5395
  Scenario: Successfully redirects to default tab if the memorized one is not visible anymore
    Given I am on the "jacket-white" product page
    And I visit the "Categories" column tab
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Consult the categories of a product
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "jacket-white" product page
    Then I should be on the "Attributes" column tab
