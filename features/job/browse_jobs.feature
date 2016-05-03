@javascript
Feature: Browse jobs
  In order to view the list of jobs
  As a product manager
  I need to be able to view a list of job profiles

  Background:
    Given a "clothing" catalog configuration

  Scenario: Successfully view job profiles depending on given permissions for manager
    Given I am logged in as "Julia"
    And I am on the exports page
    When I change the page size to 100
    Then I should see export profiles csv_clothing_attribute_export
    When I am on the imports page
    When I change the page size to 100
    Then I should see import profiles csv_clothing_product_import

  Scenario: Successfully view job profiles depending on given permissions for administrator
    Given I am logged in as "Peter"
    And I am on the exports page
    When I change the page size to 100
    Then I should see export profiles csv_clothing_attribute_export, csv_clothing_option_export
    When I am on the imports page
    And I change the page size to 100
    Then I should see import profiles csv_clothing_product_import, csv_clothing_asset_category_import
