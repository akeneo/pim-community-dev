@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As Peter
  I need to be able to restrict access to job profiles

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully revoke access to execute a job
    Given I am on the "footwear_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to execute job profile | User |
      | Permissions to edit job profile    | User |
    When I save the job profile
    Then I should be on the "footwear_product_export" export job page
    And I should not see the "Export now" button
    And I should not be able to launch the "footwear_product_export" export job
    And I am on the exports page
    And I should not see export profiles footwear_product_export

  Scenario: Successfully revoke access to edit a job
    Given I am on the "footwear_product_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to edit job profile    | User |
    When I save the job profile
    Then I should be on the "footwear_product_export" export job page
    And I should not see the "Edit" button
    And I should not be able to edit the "footwear_product_export" export job
