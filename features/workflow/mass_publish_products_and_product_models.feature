@javascript
Feature: Publish many products at once by skipping the product models
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time by skipping the product models

  Background:
    Given a "catalog_modeling" catalog configuration
    And there is no "product" in the catalog
    And there is no "product model" in the catalog
    And the following root product models:
      | code                | parent | family_variant      | categories | price          | color | description-en_US-ecommerce |
      | tshirt-unique-color |        | clothing_color_size | master_men | 10 USD, 15 EUR | blue  | A unique color t-shirt      |
    And the following products:
      | sku                  | family   | name-en_US           | categories | size | description-en_US-ecommerce |
      | tshirt-kurt-cobain-s | clothing | Tshirt Kurt Cobain S | Tshirts    | S    | A Kurt Cobain t-shirt       |
    And I am logged in as "Julia"
    And I am on the products page

  # TODO: PIM-6565 - handle correctly mass publish of product models
  Scenario: Do not publish a product model instead of a product because they have the same ID
    When I select rows tshirt-unique-color
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Publish products" operation
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    When I am on the published products page
    Then the grid should contain 0 elements
