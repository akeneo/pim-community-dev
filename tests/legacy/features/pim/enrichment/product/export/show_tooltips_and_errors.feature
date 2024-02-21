@javascript
Feature: Show tooltips and validation errors on export builder
  In order to know what I can put in the product export builder fields
  As a product manager
  I need to be able to configure a product export by reading the tooltips and validation errors

  Background:
    Given a "footwear" catalog configuration

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
