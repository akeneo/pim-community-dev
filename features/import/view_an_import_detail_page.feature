Feature: View an import detail page
  In order to know if an import is ready to be executed
  As a product manager
  I need to have access to a show import page which will present me its status

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully display the import information
    Given I am on the imports page
    When I click on the "footwear_product_import" row
    Then I should be on the "footwear_product_import" export job page
    And I should see "Import profile - Footwear product"

  Scenario: Fail to show a job for which job definition does not exist anymore
    Given the following job:
      | connector            | alias          | code                        | label                       | type   |
      | Akeneo CSV Connector | removed_import | removed_acme_product_import | Product import for Acme.com | import |
    And I am on the "removed_acme_product_import" import job page
    Then I should see "The following import does not exist anymore."
