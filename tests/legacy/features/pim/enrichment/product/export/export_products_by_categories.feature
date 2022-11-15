@javascript
Feature: Export products from any given categories
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given categories

  Background:
    Given a "default" catalog configuration
    And the following categories:
      | code                   | label-en_US               | parent                 |
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
      | 1234                   | 1234                      | default                |
    And the following family:
      | code    | requirements_ecommerce |
      | default | sku                    |
    And the following products:
      | uuid | sku                    | categories             | family  |
      | 02cfe294-2487-40ff-b1a3-8b92aa227b71 | not_categorized        |                        | default |
      | 28649f31-0263-4d9f-8f79-14cca4118015 | default                | default                | default |
      | def95e4a-498b-4783-a975-3097f589de9f | toys_games             | toys_games             | default |
      | 6e5f3767-ef85-4a30-8c1c-eb41d83e9c5e | action_figures         | action_figures         | default |
      | f10ec95a-79df-48f9-9f95-77408c3402a7 | dolls                  | dolls                  | default |
      | e8a5a8a1-467d-4e4b-966e-b2e3636c4069 | video_games            | video_games            | default |
      | 20e1a772-821a-43cd-82ee-4193e6bb3cf0 | clothing_shoes_jewelry | clothing_shoes_jewelry | default |
      | c9f75bb7-6e0b-4283-b49b-c75a6a7ffad8 | women                  | women                  | default |
      | 956ff393-5698-42d4-a7db-34acf6f9c55d | clothing               | clothing               | default |
      | 14aed9f2-dafd-4f39-9d28-5a3beb3d456b | shoes                  | shoes                  | default |
      | e7cefcc2-f286-4185-ba56-48c939fc52e4 | jewelry                | jewelry                | default |
      | fd7fbb6c-beae-421e-8f49-de6691807038 | men                    | men                    | default |
      | 396aa405-e0f1-472d-b7be-fbe0800c1d88 | product_numbered       | 1234                   | default |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"} |
      | filters   | {"structure":{"locales":["en_US"],"scope":"ecommerce"},"data":[]}         |
      | with_uuid | yes                                                                       |
    And I am logged in as "Julia"

  Scenario: Export the products from a tree
    When I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    Then I should see the text "All products"
    When I press the "Select categories" button
    Then I should see the text "Categories selection"
    And I should see the text "Master catalog"
    When I click on the "Toys & Games" category
    And I expand the "Toys & Games" category
    And I click on the "Action Figures" category
    And I click on the "1234" category
    And I press the "Confirm" button
    Then I should see the text "3 selected categories"
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      uuid;sku;categories;enabled;family;groups
      def95e4a-498b-4783-a975-3097f589de9f;toys_games;toys_games;1;default;
      6e5f3767-ef85-4a30-8c1c-eb41d83e9c5e;action_figures;action_figures;1;default;
      396aa405-e0f1-472d-b7be-fbe0800c1d88;product_numbered;1234;1;default;
      """
    When I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I fill in the following information:
      | Channel | Mobile |
    Then I should see the text "All products"
