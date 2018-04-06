@javascript
Feature: Mass edit many products at once via a form
  In order to easily organize many products
  As a product manager
  I need to be able to mass edit a selection of products greater than the batch size

  Scenario: Add products to a category
    Given the "default" catalog configuration
    And 103 empty products
    And the following category:
      | code            | parent  | label-en_US     |
      | 2018_collection | default | 2018 collection |
    And I am logged in as "Julia"
    And I am on the products grid
    And I sort by "ID" value ascending
    And I select rows "product_1"
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I expand the "default" category
    And I press the "2018 collection" button
    And I confirm mass edit
    And I wait for the "add_to_category" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "read 103"
    And I should see the text "processed 103"
    And the category of the product "product_1" should be "2018_collection"

  @unstable
  Scenario: Add product models to a category
    Given the "catalog_modeling" catalog configuration
    And the following root product models:
      | code      | family_variant      | description-en_US-ecommerce      |
      | model-col | clothing_color_size | Magnificent Cult of Luna t-shirt |
      | model-nin | clothing_size       |                                  |
    And the following sub product models:
      | code            | parent    | family_variant      | color | composition             |
      | model-col-white | model-col | clothing_color_size | white | cotton 90%, viscose 10% |
    And the following category:
      | code            | parent | label-en_US     |
      | custom_category | master | Custom category |
    And 103 empty products
    And I am logged in as "Julia"
    And I am on the products grid
    And I sort by "ID" value ascending
    And I select rows "amor"
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I expand the "master" category
    And I press the "Custom category" button
    And I confirm mass edit
    And I wait for the "add_to_category" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "read 428"
    And I should see the text "processed 428"
    And the category of the product "product_1" should be "custom_category"
