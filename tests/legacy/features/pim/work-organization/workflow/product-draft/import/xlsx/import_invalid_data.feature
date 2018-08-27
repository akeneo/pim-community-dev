@javascript
Feature: Handle import of invalid XLSX data
  In order to ease the correction of an invalid XLSX file import
  As a product manager
  I need to be able to download a XLSX file containing all invalid data of an import

  Background:
    Given the "clothing" catalog configuration

  Scenario: From a product proposal XLSX import, create an invalid data file and be able to download it
    Given the following products:
      | sku       |
      | my-jacket |
    And the following XLSX file to import:
      """
      sku;enabled;description-en_US-mobile
      my-jacket;1;My desc
      my-jacket 2;0;My desc
      """
    And the following job "xlsx_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "xlsx_clothing_product_proposal_import" import job page
    And I launch the "xlsx_clothing_product_proposal_import" import job
    And I wait for the "xlsx_clothing_product_proposal_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "xlsx_clothing_product_proposal_import" should contain:
      """
      sku;enabled;description-en_US-mobile
      my-jacket 2;0;My desc
      """
