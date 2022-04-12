@javascript @proposal-feature-enabled
Feature: Create a proposal
  In order create proposals
  As a redactor
  I need to be able to create proposals

  Scenario: Create a new proposals and be notified
    Given the "clothing" catalog configuration
    And the following product:
      | sku        | categories |
      | my-jacket  | jackets    |
      | my-jacket2 | jackets    |
      | my-jacket3 | jackets    |
    And Sandra proposed the following change to "my-jacket3":
      | field | value        |
      | SKU   | third-jacket |
    And the following CSV file to import:
      """
      sku;name-en_US;description-en_US-mobile
      my-jacket;Jacket;Description
      """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Mary"
    When I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    When I logout
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 2 new notifications
    And I should see notification:
      | type | message                                                 |
      | add  | Mary Smith has sent proposals to review from job import |
    When I click on the notification "Mary Smith has sent proposals to review from job import"
    Then I should be on the proposals index page
    And the grid should contain 1 element
    And I should see the following proposal:
      | product   | author | attribute   | original | new         |
      | my-jacket | Mary   | name        |          | Jacket      |
      | my-jacket | Mary   | description |          | Description |
