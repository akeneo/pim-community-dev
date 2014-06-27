Feature: View an export detail page
  In order to know if an export is ready to be executed
  As a product manager
  I need to have access to a show export page which will present me its status

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully display the export information
    Given I am on the exports page
    When I click on the "footwear_product_export" row
    Then I should be on the "footwear_product_export" export job page
    And I should see "Export profile - Footwear product export"

  Scenario: Successfully display the validation errors
    Given the following job:
      | connector            | alias              | code                | label                       | type   |
      | Akeneo CSV Connector | csv_product_export | acme_product_export | Product export for Acme.com | export |
    When I am on the "acme_product_export" export job page
    Then I should see "This value should not be blank." next to the channel

  Scenario: Fail to show a job instance for which the job does not exist anymore
    Given the following job:
      | connector            | alias          | code                        | label                       | type   |
      | Akeneo CSV Connector | removed_export | removed_acme_product_export | Product export for Acme.com | export |
    When I am on the "removed_acme_product_export" export job page
    Then I should see "The following export does not exist anymore."
