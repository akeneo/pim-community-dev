@javascript
Feature: Ensures the appropriate tab is displayed to the user
  In order to ease the contributor's work
  As a product manager
  I should be redirected to my previous product edit form tab

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "admin"

  @jira https://akeneo.atlassian.net/browse/PIM-6449
  Scenario: Successfully displays current tabs between product and product model edit forms
    Given I am on the "model-tshirt-divided" product model page
    And I visit the "History" column tab
    When I am on the "model-tshirt-divided-navy-blue" product model page
    Then I should be on the "History" column tab
    When I am on the "tshirt-divided-navy-blue-xxs" product page
    Then I should be on the "History" column tab
    When I visit the "Categories" column tab
    And I am on the "model-tshirt-divided-navy-blue" product model page
    Then I should be on the "Categories" column tab
