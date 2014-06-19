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
    And I fill in the following information:
      | Permissions to execute job profile | |
    When I save the job profile
    Then I should not see the "Export now" button

  Scenario: Successfully revoke access to edit a job
    Given I am on the "footwear_product_import" import job edit page
    And I fill in the following information:
      | Permissions to edit job profile | |
    And I save the job profile
    And I should see the "Edit" button
    When I am on the "Administrator" role page
    And I remove rights to Manage import profile permissions
    And I save the role
    Then I am on the "footwear_product_import" import job page
    And I should not see the "Edit" button
    And I am on the "Administrator" role page
    And I grant rights to Manage import profile permissions
    And I save the role
