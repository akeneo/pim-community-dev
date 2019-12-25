@javascript
Feature: Edit common attributes of many products and product models at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit attributes of many products and product models at once

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                     | unique | group | decimals_allowed | negative_allowed | metric_family | default_metric_unit | useable_as_grid_filter |
      | color       | Color       | pim_catalog_simpleselect | 0      | other |                  |                  |               |                     | 1                      |
      | size        | Size        | pim_catalog_simpleselect | 0      | other |                  |                  |               |                     | 1                      |
      | brand       | Brand       | pim_catalog_simpleselect | 0      | other |                  |                  |               |                     | 0                      |
      | composition | Composition | pim_catalog_text         | 0      | other |                  |                  |               |                     | 0                      |
      | weight      | Weight      | pim_catalog_metric       | 0      | other | 0                | 0                | Weight        | GRAM                | 0                      |
    And the following "Brand" attribute options: Abibas, Nyke, Ribouk
    And the following "Size" attribute options: s, m, l
    And the following "color" attribute options: white, black
    And the following family:
      | code     | label-en_US | attributes                              |
      | clothing | Clothing    | sku,color,size,brand,composition,weight |
    And the following family variants:
      | code                | family   | label-en_US                | variant-axes_1 | variant-axes_2 | variant-attributes_1   | variant-attributes_2 |
      | clothing_color_size | clothing | Clothing by color and size | color          | size           | color,composition      | size,sku,weight      |
      | clothing_size       | clothing | Clothing by color/size     | color,size     |                | color,size,composition |                      |
    And the following root product models:
      | code      | family_variant      | brand  |
      | model-col | clothing_color_size | Abibas |
      | model-nin | clothing_size       |        |
    And the following sub product models:
      | code            | parent    | family_variant      | color | composition             |
      | model-col-white | model-col | clothing_color_size | white | cotton 90%, viscose 10% |
    And the following products:
      | sku         | family   | parent          | color | size | brand  | composition | weight   |
      | col-white-m | clothing | model-col-white |       | m    |        |             | 478 GRAM |
      | nin-s       | clothing | model-nin       | black | s    |        | 100% wool   |          |
      | tool-tee    | clothing |                 | black | m    | Ribouk |             |          |
    And I am logged in as "Julia"

  @critical
  Scenario: Mass edit attributes of a product model inside a family variant with 2 levels of hierarchy
    Given I am on the products grid
    And I select rows model-col
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 Gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Nyke"
    And the product model value brand of "model-col-white" should be "Nyke"
    And the product model value composition of "model-col-white" should be "100% cotton"
    And the product value brand of "col-white-m" should be "Nyke"
    And the product "col-white-m" should have the following values:
      | composition | 100% cotton   |
      | weight      | 500.0000 GRAM |
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "read 3"
    And I should see the text "processed 3"

  @critical
  Scenario: Mass edit attributes of a sub product model inside a family variant with 2 levels of hierarchy
    Given I am on the products grid
    And I show the filter "color"
    And I filter by "color" with operator "IN LIST" and value "white"
    And I select rows model-col-white
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 Gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value composition of "model-col-white" should be "100% cotton"
    And the product "col-white-m" should have the following values:
      | composition | 100% cotton   |
      | weight      | 500.0000 GRAM |
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "read 2"
    And I should see the text "processed 2"

  @critical
  Scenario: Mass edit attributes of a product model inside a family variant with 1 levels of hierarchy
    Given I am on the products grid
    And I select rows model-nin
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-nin" should be "Nyke"
    And the product value brand of "nin-s" should be "Nyke"
    And the product value composition of "nin-s" should be "100% cotton"
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "read 2"
    And I should see the text "processed 2"

  @critical
  Scenario: Mass edit attributes of a product model and a non variant product at the same time
    Given I am on the products grid
    And I select rows model-col and tool-tee
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 Gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Nyke"
    And the product value brand of "tool-tee" should be "Nyke"
    And the product model value composition of "model-col-white" should be "100% cotton"
    And the product value composition of "tool-tee" should be "100% cotton"
    And the product "col-white-m" should have the following values:
      | weight | 500.0000 GRAM |
    And the product "tool-tee" should have the following values:
      | weight | 500.0000 GRAM |
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "read 4"
    And I should see the text "processed 4"

  @critical
  Scenario: Mass edit attributes of all selected products and product models
    Given I am on the products grid
    And I select rows model-col
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 Gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Nyke"
    And the product model value composition of "model-col-white" should be "100% cotton"
    And the product model value brand of "model-col-white" should be "Nyke"
    And the product "col-white-m" should have the following values:
      | brand       | Nyke          |
      | composition | 100% cotton   |
      | weight      | 500.0000 GRAM |
    And the product "tool-tee" should have the following values:
      | composition | 100% cotton   |
      | weight      | 500.0000 GRAM |
      | brand       | Nyke          |
    And the product model value brand of "model-nin" should be "Nyke"
    And the product "nin-s" should have the following values:
      | weight      | 500.0000 GRAM |
      | composition | 100% cotton   |
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "read 6"
    And I should see the text "processed 6"
