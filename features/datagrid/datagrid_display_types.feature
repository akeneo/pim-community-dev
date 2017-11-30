@javascript
Feature: Datagrid display
  In order to easily select different display in the datagrid
  As a regular user
  I need to be able switch datagrid display

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully display the default display type
    Given I am on the products grid
    Then I should see "List" in the display dropdown

  Scenario: Successfully select a display type
    Given I am on the products grid
    And I press "Gallery" on the "List" dropdown button
    Then I should see the "Gallery" display in the datagrid

  Scenario: Successfully keep selected display type on refresh
    Given I am on the products grid
    And I press "Gallery" on the "List" dropdown button
    And I refresh current page
    Then I should see the "Gallery" display in the datagrid

  Scenario: Successfully keep selected display type on navigation
    Given I am on the products grid
    And I press "Gallery" on the "List" dropdown button
    And I am on the "watch" product page
    And I am on the products grid
    Then I should see the "Gallery" display in the datagrid
    And I should see "Gallery" in the display dropdown

  Scenario: Successfully hide the column selector for gallery display type
    Given I am on the products grid
    And I press "Gallery" on the "List" dropdown button
    Then I should not see the text "Columns"
    And I press "List" on the "Gallery" dropdown button
    And I should see the text "Columns"

