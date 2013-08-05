@javascript
Feature: View an export detail page
  In order to know if an export is ready to be executed
  As a user
  I need to have access to a show export page which will present me its status

  Scenario: Successfully display the export information
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    And I am on the exports page
    When I click on the "acme_product_export" row
    Then I should be on the "acme_product_export" export job page

  Scenario: Successfully display the validation errors
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    And I am on the "acme_product_export" export job page
    Then I should see "This value should not be blank." next to the channel

  Scenario: Fail to show a job for which job definition does not exist anymore
    Given the following job:
      | connector | alias          | code                        | label                       | type   |
      | Akeneo    | removed_export | removed_acme_product_export | Product export for Acme.com | export |
    Given I am logged in as "admin"
    And I am on the "removed_acme_product_export" export job page
    Then I should see "The following export does not exist anymore."
