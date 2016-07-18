@javascript
Feature: Export products from any given categories
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given categories

  Background:
    Given a "default" catalog configuration
    And the following categories:
      | code                   | label_en_US               | parent                 |
      | toys_games             | Toys & Games              | default                |
      | action_figures         | Action Figures            | toys_games             |
      | dolls                  | Dolls                     | toys_games             |
      | video_games            | Video Games               | default                |
      | clothing_shoes_jewelry | Clothing, Shoes & Jewelry |                        |
      | women                  | Women                     | clothing_shoes_jewelry |
      | clothing               | Clothing                  | women                  |
      | shoes                  | Shoes                     | women                  |
      | jewelry                | Jewelry                   | women                  |
      | men                    | Men                       | clothing_shoes_jewelry |
    And the following family:
      | code    | requirements_ecommerce |
      | default | sku                    |
    And the following products:
      | sku                    | categories             | family  |
      | not_categorized        |                        | default |
      | default                | default                | default |
      | toys_games             | toys_games             | default |
      | action_figures         | action_figures         | default |
      | dolls                  | dolls                  | default |
      | video_games            | video_games            | default |
      | clothing_shoes_jewelry | clothing_shoes_jewelry | default |
      | women                  | women                  | default |
      | clothing               | clothing               | default |
      | shoes                  | shoes                  | default |
      | jewelry                | jewelry                | default |
      | men                    | men                    | default |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"

  # We should handle this case with validation
  @skip
  Scenario: Export the products from a tree
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["en_US"], "scope": "ecommerce"}, "data": [{"field": "categories.code", "operator": "IN", "value": ["toys_games", "dolls", "women"]}, {"field": "completeness", "operator": ">=", "value": 100}]} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups
      toys_games;toys_games;1;default;
      dolls;dolls;1;default;
      """

  Scenario: Export the products from a tree using the UI
    When I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    Then I should see the text "All products"
    When I press the "Select categories" button
    Then I should see the text "Categories selection"
    When I click on the "toys_games" category
    And I expand the "toys_games" category
    And I click on the "action_figures" category
    And I press the "Confirm" button
    Then I should see the text "2 categories selected"
    When I press the "Save" button
    And I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups
      toys_games;toys_games;1;default;
      action_figures;action_figures;1;default;
      """
    When I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I fill in the following information:
      | Channel | Mobile |
    Then I should see the text "All products"
