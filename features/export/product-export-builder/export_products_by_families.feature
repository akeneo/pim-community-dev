@javascript
Feature: Export products according to their families
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their families

  Background:
    Given an "footwear" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5952
  Scenario: Display default messages when no family are selected
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    Then the export content field "family" should contain "No condition on families"

  @jira https://akeneo.atlassian.net/browse/PIM-6162
  Scenario: View families already selected
    Given I am on the "csv_footwear_product_export" export job edit page
    When I visit the "Content" tab
    And I filter by "family" with operator "" and value "Boots,Heels,Sneakers"
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I press the "Edit" button
    And I should see the text "Boots Heels Sneakers"
