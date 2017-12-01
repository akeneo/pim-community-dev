@javascript
Feature: Edit common attributes of many products and product models at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit attributes of many products and product models at once

  Background:
    Given the "catalog_modeling" catalog configuration
    And there is no "product" in the catalog
    And there is no "product model" in the catalog
    And the following "Brand" attribute options: Abibas, Nyke, Ribouk
    And the following root product models:
      | code      | family_variant      | brand  |
      | model-col | clothing_color_size | abibas |
      | model-nin | clothing_size       |        |
    And the following sub product models:
      | code            | parent    | family_variant      | color | composition             |
      | model-col-white | model-col | clothing_color_size | white | cotton 90%, viscose 10% |
    And the following products:
      | sku         | family   | parent          | color | size | brand  | composition | weight   |
      | col-white-m | clothing | model-col-white |       | m    |        |             | 478 GRAM |
      | nin-s       | clothing | model-nin       | black | s    |        | 100% wool   |          |
      | tool-tee    | clothing |                 | black | m    | ribouk |             |          |
    And I am logged in as "Julia"

  Scenario: Mass edit attributes of a product model inside a family variant with 2 levels of hierarchy
    Given I am on the products grid
    And I select rows model-col
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Nyke"
    Then the product model value brand of "model-col-white" should be "Nyke"
    Then the product value brand of "col-white-m" should be "Nyke"
    Then the product model value composition of "model-col-white" should be "100% cotton"
    Then the product value composition of "col-white-m" should be "100% cotton"
    Then the product value weight of "col-white-m" should be "500 gram"

  Scenario: Mass edit attributes of a sub product model inside a family variant with 2 levels of hierarchy
    Given I am on the products grid
    And I filter by "color" with operator "IN LIST" and value "white"
    And I select rows model-col-white
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value composition of "model-col-white" should be "100% cotton"
    Then the product value composition of "col-white-m" should be "100% cotton"
    Then the product value weight of "col-white-m" should be "500 gram"

  Scenario: Mass edit attributes of a product model inside a family variant with 1 levels of hierarchy
    Given I am on the products grid
    And I select rows model-nin
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-nin" should be "Nyke"
    Then the product value brand of "nin-s" should be "Nyke"
    Then the product model value composition of "nin-s" should be "100% cotton"

  Scenario: Mass edit attributes of a product model and a non variant product at the same time
    Given I am on the products grid
    And I select rows model-col and tool-tee
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Nyke"
    Then the product model value brand of "tool-tee" should be "Nyke"
    Then the product model value composition of "model-col-white" should be "100% cotton"
    Then the product model value composition of "tool-tee" should be "100% cotton"
    Then the product model value weight of "col-white-m" should be "500 gram"
    Then the product model value weight of "tool-tee" should be "500 gram"

  Scenario: It does not update ancestors' attributes
    Given I am on the products grid
    And I filter by "color" with operator "IN LIST" and value "white"
    And I filter by "size" with operator "IN LIST" and value "m"
    And I select rows col-white-m
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Brand attribute
    And I change the "Brand" to "Nyke"
    And I display the Composition attribute
    And I change the "Composition" to "100% cotton"
    And I display the Weight attribute
    And I change the "Weight" to "500 gram"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product model value brand of "model-col" should be "Abibas"
    Then the product model value brand of "model-col-white" should be "Abibas"
    Then the product model value composition of "model-col-white" should be "cotton 90%, viscose 10%"
    Then the product model "model-col" should not have the following values "composition, weight"
    Then the product model "model-col-white" should not have the following values weight
