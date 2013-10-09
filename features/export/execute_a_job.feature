Feature: Execute a job
  In order to launch an export
  As a user
  I need to be able to execute a valid export

  Scenario: Fail to see the execute button of a job with validation errors
    Given the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    When I am on the "acme_product_export" export job page
    Then I should not see the "Execute" link

  Scenario: Fail to launch a job with validation errors
    Given the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    When I launch the "acme_product_export" export job
    Then I should not see "The export is running."
    And I should not see "An error occured during the export execution."

  @javascript
  Scenario: Successfully launch a valid job
    Given the following attributes:
      | code | label |
      | name | Name  |
    And the following family:
      | code     |
      | hardware |
    And the following product:
      | sku | family   |
      | 001 | hardware |
    And the following product values:
      | product | attribute | value    |
      | 001     | name      | computer |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    And the following category:
      | code   | label  | parent  | products |
      | memory | Memory | default | 001      |
    And the following job "acme_product_export" configuration:
      | element | property      | value      |
      | reader  | channel       | mobile     |
      | writer  | directoryName | /tmp/      |
      | writer  | fileName      | export.csv |
    And I launched the completeness calculator
    And I am logged in as "admin"
    And I am on the "acme_product_export" export job page
    When I launch the "acme_product_export" export job
    Then I should see "Execution details"
    And file "/tmp/export.csv" should exist
    And an email to "admin@example.com" should have been sent
