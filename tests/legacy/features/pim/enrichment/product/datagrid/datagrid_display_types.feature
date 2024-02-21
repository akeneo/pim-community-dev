@javascript
Feature: Datagrid display
  In order to easily select different display in the datagrid
  As a regular user
  I need to be able switch datagrid display

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Mary"
    
  Scenario: Successfully select a display type
    Given I am on the products grid
    And I press "Gallery" on the "List" dropdown button
    Then I should see the "Gallery" display in the datagrid
