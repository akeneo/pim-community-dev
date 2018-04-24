@javascript
Feature: Import XLSX products with numbers
  In order to use existing product information
  As a product manager
  I need to be able to import products containing numbers with XLSX files

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code          | attributes                |
      | number_family | number_in_stock,rate_sale |
    And I am logged in as "Julia"

  @info If Excel opens a file containing numeric strings that match its locale config it will be transformed into real numbers
  Scenario: Successfully import an XLSX file of products with real integers
    Given I am on the "xlsx_footwear_product_import_fr" import job page
    When I upload and import the file "products_with_integers.xlsx"
    And I wait for the "xlsx_footwear_product_import_fr" job to finish
    Then I should see the text "created 2"
    And attribute number_in_stock of "rangers001" should be "27"
    And attribute number_in_stock of "rangers002" should be "84"

  Scenario: Successfully import an XLSX file of products with real floats with a job expecting comma as decimal separator
    Given I am on the "xlsx_footwear_product_import_fr" import job page
    When I upload and import the file "products_with_floats.xlsx"
    And I wait for the "xlsx_footwear_product_import_fr" job to finish
    Then I should see the text "created 2"
    And attribute rate_sale of "rangers001" should be "0.87"
    And attribute rate_sale of "rangers002" should be "1.49"

  Scenario: Successfully import an XLSX file of products with numeric strings and valid separators
    Given I am on the "xlsx_footwear_product_import_fr" import job page
    When I upload and import the file "products_with_numeric_strings.xlsx"
    And I wait for the "xlsx_footwear_product_import_fr" job to finish
    Then I should see the text "created 2"
    And attribute rate_sale of "rangers001" should be "0.87"
    And attribute rate_sale of "rangers002" should be "1.49"

  Scenario: Skip products with numeric strings and invalid separators during an XLSX import
    Given I am on the "xlsx_footwear_product_import_fr" import job page
    When I upload and import the file "products_with_invalid_numeric_strings.xlsx"
    And I wait for the "xlsx_footwear_product_import_fr" job to finish
    Then I should see the text "created 1"
    And I should see the text "skipped 1"
    And I should see the text "values[rate_sale]: This type of value expects the use of a comma (,) to separate decimals.: 0.87"
    And attribute rate_sale of "rangers002" should be "1.49"
