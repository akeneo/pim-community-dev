@javascript
Feature: Display proposals after import
  In order to summarize proposals
  As a product manager
  I need to be able to have only one notification after an import

  Background:
    Given the "clothing" catalog configuration
    And the following product:
      | sku        | categories      | family  |
      | my-jacket  | 2014_collection | jackets |
      | my-jacket2 | 2014_collection | jackets |
      | my-jacket3 | 2014_collection | jackets |
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
    And the following CSV file to import:
      """
      sku;name-en_US
      my-jacket;My jacket
      my-jacket2;My jacket2
      """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |

  Scenario: Successfully display a notification for owner
    Given I am logged in as "Mary"
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    And I logout
    And I am logged in as "Julia"
    When I am on the dashboard index page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                                           |
      | add  | New proposals from import Mary Smith has sent proposals to review from job import |

  Scenario: Successfully display a notification for author
    Given I am logged in as "Julia"
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    When I am on the dashboard index page
    Then I should have 2 new notifications
    And I should see notifications:
      | type    | message                                                                |
      | add     | New proposals from import You have proposals to review from job import |
      | success | Import Import Demo CSV product draft import finished                   |
