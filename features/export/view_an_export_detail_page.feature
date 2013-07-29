@javascript
Feature: View an export detail page
  In order to know if an export is ready to be executed
  As an user
  I need to have access to a show export page which will present me its status

  Scenario: Successfully display the export information
    Given the following export job:
      | connector | alias          | code                | label                       |
      | Akeneo    | product_export | acme_product_export | Product export for Acme.com |
    Given I am logged in as "admin"
    And I am on the exports index page
    When I click on the "acme_product_export" row
    Then I should be on the "acme_product_export" export job page
