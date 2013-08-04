Feature: Execute a job
  In order to launch an export
  As a user
  I need to be able to execute a valid export

  Scenario: Fail to see the execute button of a job with validation errors
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    When I am on the "acme_product_export" export job page
    Then I should not see the "Execute" link

  Scenario: Fail to launch a job with validation errors
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    When I launch the "acme_product_export" export job
    Then I should not see "The export has been successfully executed."
    And I should not see "An error occured during the export execution."

  Scenario: Successfully launch a valid job
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com | export |
    And the following job "acme_product_export" configuration:
      | element | property | value           |
      | reader  | channel  | mobile          |
      | writer  | path     | /tmp/export.csv |
    And I am logged in as "admin"
    And I am on the "acme_product_export" export job page
    When I launch the export job
    Then I should see "The export has been successfully executed."
    And file "/tmp/export.csv" should exist
