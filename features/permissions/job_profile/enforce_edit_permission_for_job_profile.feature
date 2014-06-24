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
      | Permissions to edit job profile    | |
    When I save the job profile
    Then I should not see the "Export now" button
    And I should not launch the "footwear_product_export" export job
