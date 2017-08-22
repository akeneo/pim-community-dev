@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As an administrator
  I need to be able to define permissions for job profiles

  Scenario: Successfully display the fields for a csv job profile permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    And I am on the "csv_clothing_product_import" import job edit page
    When I visit the "Permissions" tab
    Then I should see the Allowed to execute job profile and Allowed to edit job profile fields
    When I fill in the following information:
      | Allowed to execute job profile | IT support |
      | Allowed to edit job profile    | IT support |
    And I save the job profile
    Then I should not see the text "There are unsaved changes."
    And I should be on the "csv_clothing_product_import" import job page
    And I should see the "Edit" button
    And I should see the "Import now" button

  Scenario: Successfully display the fields for an XLSX job profile permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    And I am on the "xlsx_clothing_product_import_with_rules" import job edit page
    When I visit the "Permissions" tab
    Then I should see the Allowed to execute job profile and Allowed to edit job profile fields
    When I fill in the following information:
      | Allowed to execute job profile | IT support |
      | Allowed to edit job profile    | IT support |
    And I save the job profile
    Then I should not see the text "There are unsaved changes."
    And I should be on the "xlsx_clothing_product_import_with_rules" import job page
    And I should see the "Edit" button
    And I should see the "Import now" button
