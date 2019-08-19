@javascript
Feature: Ensures acl are respected on the export profile tabs
  In order to give more access to export configuration (content part) for Julia, without giving her all the settings access (Properties tab)
  As peter
  I would like to manage permissions on the export profile tabs

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab

  Scenario: Disable show general property and show content tab
    Given I revoke rights to resources Show an export profile general properties
    And I revoke rights to resources Show an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job page
    Then I should not see the text "General properties"
    And I should not see the text "Content"

  Scenario: Disable edit general property right and update Content tab
    Given I revoke rights to resources Edit an export profile general properties
    And I grant rights to resources Edit an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    Then I should not see the text "General properties"
    When I filter by "family" with operator "" and value "Boots"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I press the "Edit" button
    Then I should see the text "Boots"
