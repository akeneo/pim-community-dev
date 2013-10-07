@javascript
Feature: Execute a job
  In order to launch an import
  As Julia
  I need to be able to execute a valid export

  Background:
    Given the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |

  Scenario: Fail to see the import button of a job with validation errors
    Given I am logged in as "Julia"
    When I am on the "acme_product_import" import job page
    Then I should not see the "Import now" link
