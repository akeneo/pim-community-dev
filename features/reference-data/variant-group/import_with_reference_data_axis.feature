@javascript
Feature: Import variant group involving reference data
  In order to manage variant group with a custom catalog
  As a product manager
  I need to be able to import a variant group with reference data values

  Background:
    Given a "footwear" catalog configuration
    And the following reference data:
      | type   | code | label |
      | color  | red  | Red   |
      | color  | blue | Blue  |
    And the following product groups:
      | code   | label  | axis       | type    |
      | jacket | Jacket | sole_color | VARIANT |
    And the following products:
      | sku       | sole_color | groups |
      | my-jacket | red        | jacket |
    Then I am logged in as "Julia"

  Scenario: Import a product in a variant group identical to one already in database
    Given the following CSV file to import:
      """
      sku;sole_color;groups
      my-jacket;red;jacket
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "skipped product (no differences) 1"

  Scenario: Import a product in a variant group with a reference data as variation axis
    Given the following CSV file to import:
      """
      sku;sole_color;groups
      another-jacket;blue;jacket
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 2 product

  Scenario: Import a product in a variant group with a reference data as variation axis which already exists
    Given the following CSV file to import:
      """
      sku;sole_color;groups
      another-jacket;red;jacket
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "Group \"[jacket]\" already contains another product with values \"sole_color: Red\": another-jacket"
