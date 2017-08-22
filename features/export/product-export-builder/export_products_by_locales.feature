@javascript
Feature: Export products according to a locale policy
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a given locale

  Background:
    Given a "default" catalog configuration
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And I am logged in as "Julia"

  @ce
  Scenario: Selecting a channel from the export profile updates the locale choices
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["fr_FR"], "scope": "mobile"},"data":[{"field":"completeness","operator":"=","value":"100"}]} |
    And I am on the "csv_product_export" export job edit page
    When I visit the "Content" tab
    Then I should see the text "French (France)"
    When I fill in the following information:
      | Channel | Ecommerce |
    Then I should see the text "French (France) English (United States)"
