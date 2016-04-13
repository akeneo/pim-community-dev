@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As an administrator
  I need to be able to define permissions for job profiles

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    And I am on the "clothing_product_import" import job edit page

  Scenario: Successfully display the fields for job profile permissions
    Given I visit the "Permissions" tab
    Then I should see the Allowed to execute job profile and Allowed to edit job profile fields
    When I fill in the following information:
      | Allowed to execute job profile | IT support |
      | Allowed to edit job profile    | IT support |
    And I save the job profile
    Then I should be on the "clothing_product_import" import job page
    And I should see the "Edit" button
    And I should see the "Import now" button
