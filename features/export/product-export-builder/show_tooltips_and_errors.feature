@javascript
Feature: Show tooltips and validation errors on export builder
  In order to know what I can put in the product export builder fields
  As a product manager
  I need to be able to configure a product export by reading the tooltips and validation errors

  Background:
    Given a "footwear" catalog configuration

  Scenario: Successfully show tooltips on export builder
    Given I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Global settings" tab
    Then I should see the tooltip "Determine the decimal separator"
    And I should see the tooltip "Determine the format of date fields"
    And I should see the tooltip "Where to write the generated file on the file system"
    And I should see the tooltip "One character used to set the field delimiter"
    And I should see the tooltip "One character used to set the field enclosure"
    And I should see the tooltip "Whether or not to print the column name"
    And I should see the tooltip "Whether or not to export product files and images"
    When I visit the "Content" tab
    Then I should see the tooltip "The channel defines the scope for product values, the locales used to select data, and the tree used to select products."
    And I should see the tooltip "The locales defines the localized product values to export. Ex: only product information in French."
    And I should see the tooltip "Select the product information to export. Ex: only the technical attributes."
    And I should see the tooltip "Select the products to export by their family. Ex: Export only the shoes and dresses."
    And I should see the tooltip "Select the products to export by their status. Ex: Export products whatsoever their status."
    And I should see the tooltip "Select the products to export by their completeness."
    And I should see the tooltip "Use the product categories in the tree (defined by the channel above) to select the products to export"
    And I should see the tooltip "Use the product identifiers to export separated by commas, spaces or line breaks"

  Scenario: Successfully show error messages on export builderCompleteness
    Given I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I change the Label to ""
    And I visit the "Global settings" tab
    And I change the Delimiter to ""
    And I change the Enclosure to ""
    And I visit the "Properties" tab
    When I press "Save"
    Then I should see the text "The job profile could not be updated."
    And I should see the text "This value should not be blank."
    When I visit the "Global settings" tab
    And I should see the text "The value must be one of , or ; or |"
    And I should see the text "The value must be one of \" or '"
