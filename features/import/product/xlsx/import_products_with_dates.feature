@javascript
Feature: Import XLSX products with dates
  In order to use existing product information
  As a product manager
  I need to be able to import products containing dates with XLSX files

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code        | attributes      |
      | date_family | destocking_date |
    And I am logged in as "Julia"

  @info If Excel opens a file containing dates that match its locale config it will be transformed into a timestamp
  Scenario: Successfully import an XLSX file of products with dates as timestamps
    Given I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "products_with_timestamp_dates.xlsx"
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then I should see the text "created 2"
    And attribute destocking_date of "rangers001" should be "2016-06-24"
    And attribute destocking_date of "rangers002" should be "2016-07-01"

  Scenario: Successfully import an XLSX file of products with dates as strings
    Given I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "products_with_string_dates.xlsx"
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then I should see the text "created 2"
    And attribute destocking_date of "rangers001" should be "2016-06-24"
    And attribute destocking_date of "rangers002" should be "2016-07-01"

  Scenario: Skip products with invalid string dates during an XLSX import
    Given I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "products_with_invalid_string_dates.xlsx"
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then I should see the text "created 1"
    And I should see the text "skipped 1"
    And I should see the text "values[destocking_date]: This type of value expects the use of the format yyyy-MM-dd for dates.: 2016-06-31"
    And attribute destocking_date of "rangers002" should be "2016-07-01"
