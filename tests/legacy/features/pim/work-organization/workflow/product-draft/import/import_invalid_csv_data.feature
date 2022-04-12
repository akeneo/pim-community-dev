@javascript @proposal-feature-enabled
Feature: Handle import of invalid CSV data
  In order to ease the correction of an invalid CSV file import
  As a product manager
  I need to be able to download a CSV file containing all invalid data of an import

  Background:
    Given the "clothing" catalog configuration

  Scenario: From a product proposal CSV import, create an invalid data file and be able to download it
    Given the following products:
      | sku       |
      | my-jacket |
    And the following CSV file to import:
      """
      sku;enabled;description-en_US-mobile
      my-jacket;1;My desc
      my-jacket 2;0;My desc
      """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the "csv_clothing_product_proposal_import" import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    Then I should see "Download invalid data" on the "Download generated files" dropdown button
    And the invalid data file of "csv_clothing_product_proposal_import" should contain:
      """
      sku;enabled;description-en_US-mobile
      my-jacket 2;0;My desc
      """
