@javascript
Feature: Execute an import with template values
  In order to update existing product information
  As a product manager
  I need to be able to import variant group with template data in product values

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Name and Description
    And I fill in the following information:
      | Name | The T-Shirt |
    And I save the variant group

  @skip @info Will be removed in PIM-6444
  Scenario: Import a variant group with empty data from the template should not change the structure of the variant
    Given the following CSV file to import:
      """
      code;type;axis;description-de_DE-ecommerce;description-de_DE-print;description-en_GB-ecommerce;description-en_GB-tablet;description-en_US-ecommerce;description-en_US-print;description-en_US-tablet;description-fr_FR-ecommerce;label-de_DE;label-en_GB;label-en_US;label-fr_FR;name-de_DE;name-en_GB;name-en_US;name-fr_FR;type
      tshirts;VARIANT;color,size;;;;;;;;;;;;;;;The T-Shirt;;variant
      hoodies;VARIANT;color,size;;;;;;;;;;;;;;;Hoodies;;variant
      """
    And the following job "variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "variant_group_import" import job page
    And I launch the import job
    And I wait for the "variant_group_import" job to finish
    When I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    Then I should see the text "Description"
    When I am on the "hoodies" variant group page
    Then I should not see "Description"

  @skip @info Will be removed in PIM-6444
  Scenario: Import a variant group with partial data should not change the structure of the variant
    Given the following CSV file to import:
    """
    code;axis;label-de_DE;label-en_GB;label-en_US;label-fr_FR;name-de_DE;name-en_GB;name-en_US;name-fr_FR;legend-en_US;type
    tshirts;color,size;;;;;;;The T-Shirt;;The legend;variant
    """
    And the following job "variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "variant_group_import" import job page
    And I launch the import job
    And I wait for the "variant_group_import" job to finish
    When I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    Then I should see the text "Description"
    When I visit the "Media" group
    Then I should see the text "Legend"
