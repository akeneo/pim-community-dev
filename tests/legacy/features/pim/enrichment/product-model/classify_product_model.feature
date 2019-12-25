@javascript
Feature: Classify a product model
  In order to classify product models
  As a product manager
  I need to associate a product model to categories

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

  Scenario: Count sub product model categories
    Given I edit the "model-nin-black" product model
    When I visit the "Categories" column tab
    And I visit the "Master" tab
    Then I should see 3 category count
    And the category of the product model "model-nin-black" should be "tshirts, summer and spring"
