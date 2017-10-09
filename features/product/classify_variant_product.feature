@javascript
Feature: Classify a variant product
  In order to classify products
  As a product manager
  I need to associate a variant product to categories

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following categories:
      | code         | label_en_US  | parent  |
      | long_sleeves | Long sleeves | tshirts |
      | seasons      | Seasons      | tshirts |
      | summer       | Summer       | seasons |
      | spring       | Spring       | seasons |
    And the following root product models:
      | code      | family_variant      | categories |
      | model-nin | clothing_color_size | tshirts    |
    And the following sub product models:
      | code            | parent    | family_variant      | categories    | color |
      | model-nin-black | model-nin | clothing_color_size | summer,spring | black |
    And the following products:
      | sku         | parent          | family   | categories   | size |
      | nin-black-m | model-nin-black | clothing | long_sleeves | m    |
    And I am logged in as "Julia"

  Scenario: Count variant product categories
    Given I edit the "nin-black-m" product
    When I visit the "Categories" column tab
    And I visit the "Master" tab
    Then I should see 4 category count
    And the category of the product "nin-black-m" should be "tshirts, summer, spring and long_sleeves"
