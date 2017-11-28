@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As an administrator
  I need to be able to restrict access to job profiles

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  @unstable
  Scenario: Successfully revoke access to execute a job
    Given I am on the "csv_clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to execute job profile | Redactor |
      | Allowed to edit job profile    | Redactor |
    When I save the job profile
    Then I should not see the text "There are unsaved changes."
    Then I should be on the "csv_clothing_product_export" export job page
    And I should not see the "Export now" button
    And I am on the exports page
    And I should not see export profiles csv_clothing_product_export

  Scenario: Successfully revoke access to edit a job
    Given I am on the "csv_clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit job profile | Redactor |
    When I save the job profile
    Then I should not see the text "There are unsaved changes."
    And I should be on the "csv_clothing_product_export" export job page
    And I should not see the "Edit" button
