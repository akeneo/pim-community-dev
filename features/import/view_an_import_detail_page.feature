@javascript
Feature: View an import detail page
  In order to know if an import is ready to be executed
  As a user
  I need to have access to a show import page which will present me its status

  Scenario: Successfully display the import information
    Given the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    Given I am logged in as "admin"
    And I am on the imports page
    When I click on the "acme_product_import" row
    Then I should see "Import / Product import for Acme.com"

  Scenario: Fail to show a job for which job definition does not exist anymore
    Given the following job:
      | connector            | alias          | code                        | label                       | type   |
      | Akeneo CSV Connector | removed_import | removed_acme_product_import | Product import for Acme.com | import |
    Given I am logged in as "admin"
    And I am on the "removed_acme_product_import" import job page
    Then I should see "The following import does not exist anymore."
