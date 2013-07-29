@javascript
Feature: Execute a job
  In order to launch an export
  As an user
  I need to be able to execute a valid export

  Scenario: Fail to see the execute button of a job with validation errors
    Given the following export job:
      | connector | alias          | code                | label                       |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com |
    Given I am logged in as "admin"
    When I am on the "acme_product_export" export job page
    Then I should not see the "Execute" link

  Scenario: Fail to launch a job with validation errors
  Scenario: Successfully launch a valid job
