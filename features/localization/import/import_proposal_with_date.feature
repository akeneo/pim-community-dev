@javascript
Feature: Import proposals with a date
  In order to generate proposals
  As a redactor
  I need to be able to import proposals with a date

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku     | categories        |
      | SKU-001 | summer_collection |
      | SKU-002 | summer_collection |
      | SKU-003 | summer_collection |
      | SKU-004 | summer_collection |
      | SKU-005 | summer_collection |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file (with date as dd/mm/yyyy) with a date attribute
    Given the following CSV file to import:
      """
      sku;release_date-mobile;name-en_US
      SKU-001;19/08/1977;x-wing
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath   | %file to import% |
      | dateFormat | dd/MM/yyyy       |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 1 proposal
    And I should get the following proposal:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"x-wing"}],"release_date":[{"locale":null,"scope":"mobile","data":"1977-08-19"}]}} |
    When I am on the proposals page
    Then the grid should contain 1 element
    And I should see entity SKU-001

  Scenario: Skip product with a date format different from configuration
    Given the following CSV file to import:
      """
      sku;release_date-mobile;name-en_US
      SKU-001;19/08/1977;
      SKU-002;19-08-1977;
      SKU-003;1977/08/19;
      SKU-004;1977-08-19;Tie Fighter
      SKU-005;;Millenium Falcon
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath   | %file to import% |
      | dateFormat | yyyy-MM-dd       |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 2 proposals
    And I should see "skipped 3"
    And I should see "values[release_date-mobile]: This type of value expects the use of the format yyyy-MM-dd for dates.: 19/08/1977"
    And I should see "values[release_date-mobile]: This type of value expects the use of the format yyyy-MM-dd for dates.: 19-08-1977"
    And I should see "values[release_date-mobile]: This type of value expects the use of the format yyyy-MM-dd for dates.: 1977/08/19"
    And I should get the following proposal:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-004 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Tie Fighter"}],"release_date":[{"locale":null,"scope":"mobile","data":"1977-08-19"}]}} |
      | clothing_product_proposal_import | SKU-005 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Millenium Falcon"}]}}                                                                  |
