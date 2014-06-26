@javascript
Feature: Define permissions for a job profile
  In order to be able to restrict access to job profiles
  As Peter
  I need to be able to define permissions for job profiles

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the "footwear_product_import" import job edit page

  Scenario: Successfully display the fields for job profile permissions
    Given I visit the "Permissions" tab
    Then I should see the Permissions to execute job profile and Permissions to edit job profile fields
    When I fill in the following information:
      | Permissions to execute job profile | Administrator |
      | Permissions to edit job profile    | Administrator |
    And I save the job profile
    Then I should be on the "footwear_product_import" import job page
    And I should see the "Edit" button
    And I should see the "Export now" button
