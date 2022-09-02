@acceptance-back
Feature: Enrich a table attribute value
  In order to enrich my products
  As a product manager
  I need to be able to see validation errors when enriching a table

  Background:
    Given an authenticated user
    And the following attributes:
      | code        | type                     | table_configuration                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
      | sku         | pim_catalog_identifier   |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
      | nutrition   | pim_catalog_table        | [{"id": "ingredient_f6492fb4-d815-4d30-a912-8db321a3e38a", "code": "ingredient", "data_type": "select", "labels": {"en_US": "Ingredient"}, "options": [{"code": "sugar", "labels": {"fr_FR": "Sucre"}}, {"code": "salt"}]}, {"id": "quantity_f967d82a-b54c-41da-959e-1fa43124afee", "code": "quantity", "data_type": "number"}, {"id": "is_allergenic_c8ef6a66-cca8-49c6-9448-b71a48f3636b", "code":"isAllergen", "data_type":"boolean"}, {"id": "comments_d39d3c48-46e6-4744-8196-56e08563fd46", "code":"comments", "data_type":"text"}] |
    And the following select options:
      | attribute_code | column_code | options                                                             |
      | nutrition      | ingredient  | [{"code": "sugar", "labels": {"fr_FR": "Sucre"}}, {"code": "salt"}] |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"

  Scenario: Providing a table with a non existing column should raise an error
    When a product is created with values:
      | attribute   | json_data                                                                  |
      | nutrition   | [{"ingredient": "sugar", "quantity": 20, "non_existing_column": "foobar"}] |
    Then the error 'The "non_existing_column" column does not exist' is raised

  Scenario: Providing a table with non existing columns should raise an error
    When a product is created with values:
      | attribute | json_data                                                                                  |
      | nutrition | [{"ingredient": "sugar", "quantity": "20", "non_existing_column": "foobar", "foo": "bar"}] |
    Then the error 'The "non_existing_column, foo" columns do not exist' is raised

  Scenario: Filling a cell with the wrong data type raises an error
    When a product is created with values:
      | attribute | json_data                                                                        |
      | nutrition | [{"ingredient": 1, "quantity": "abcdef", "isAllergen": "ghijk", "comments": 25}] |
    Then the error 'The "quantity" column expects a numeric, string given' is raised
    And the error 'The "ingredient" column expects a string, integer given' is raised
    And the error 'The "isAllergen" column expects a boolean, string given' is raised
    And the error 'The "comments" column expects a string, integer given' is raised

  @only-ee
  Scenario: Filling a cell with the wrong data type raises an error
    Given the following attributes:
      | code        | type                     | table_configuration                                                                                                                                                                                                                                    |
      | brands      | pim_catalog_table        | [{"id": "brand_f6492fb4-d815-4d30-a912-8db321a3e38a", "code": "brand", "data_type": "reference_entity", "reference_entity_identifier": "brands"}, {"id": "quantity_f6492fb4-d815-4d30-a912-8db321a3e39a", "code": "quantity", "data_type": "number"}]  |
    When a product is created with values:
      | attribute | json_data       |
      | brands    | [{"brand": 1}]  |
    Then the error 'The "brand" column expects a string, integer given' is raised

  @only-ee
  Scenario: Providing a non existing record for a reference entity cell should raise an error
    Given the brands reference entity
    And the following records:
      | ref entity  | code    |
      | brands      | Ferrari |
      | brands      | Dacia   |
    And the following attributes:
      | code        | type              | table_configuration                                                                                                                                                                                                                                   |
      | brands      | pim_catalog_table | [{"id": "brand_f7492fb4-d815-4d30-a912-8db321a3e38a", "code": "brand", "data_type": "reference_entity", "reference_entity_identifier": "brands"}, {"id": "quantity_f7492fb4-d815-4d30-a912-8db321a3e39a", "code": "quantity", "data_type": "number"}] |
    When a product is created with values:
      | attribute | json_data               |
      | brands    | [{"brand": "Renault"}]  |
    Then the error 'The "Renault" record in the "brands" reference entity does not exist' is raised

  @only-ee
  Scenario: Providing a existing record for a reference entity cell should not raise any error
    Given the brands reference entity
    And the following records:
      | ref entity  | code    |
      | brands      | Ferrari |
      | brands      | Dacia   |
    And the following attributes:
      | code        | type              | table_configuration                                                                                                                                                                                                                                   |
      | brands      | pim_catalog_table | [{"id": "brand_f7492fb4-d815-4d30-a912-8db321a3e38a", "code": "brand", "data_type": "reference_entity", "reference_entity_identifier": "brands"}, {"id": "quantity_f7492fb4-d815-4d30-a912-8db321a3e39a", "code": "quantity", "data_type": "number"}] |
    When a product is created with values:
      | attribute | json_data               |
      | brands    | [{"brand": "Ferrari"}]  |
    Then no error is raised

  Scenario: Not filling the first column raises an error
    When a product is created with values:
      | attribute | json_data         |
      | nutrition | [{"quantity": 1}] |
    Then the error 'The "ingredient" column is mandatory' is raised

  Scenario: Providing a valid table should not raise any error
    When a product is created with values:
      | attribute | json_data         |
      | nutrition | [{"ingredient": "sugar", "quantity": 1}] |
    Then no error is raised

  Scenario: Providing a valid table should not raise any error
    When a product is created with values:
      | attribute | json_data                                                                      |
      | nutrition | [{"ingredient": "sugar", "quantity": 1, "isAllergen":true, "comments": "foo"}] |
    Then no error is raised

  Scenario: Providing a valid table should not raise any error with case insensitive
    When a product is created with values:
      | attribute | json_data                                                                      |
      | nutrition | [{"INGredient": "SUGar", "quantity": 1, "ISAllergen":true, "COMMENTS": "foo"}] |
    Then no error is raised

  Scenario: Providing a non existent option for a select cell should raise an error
    When a product is created with values:
      | attribute | json_data                                                                       |
      | nutrition | [{"ingredient": "butter", "quantity": 1, "isAllergen":true, "comments": "foo"}] |
    Then the error 'Make sure you only use existing option codes, current value: "butter"' is raised

  Scenario: Providing a valid table using numerics as column codes and select option codes should not raise any error
    Given the following attributes:
      | code        | type                     | table_configuration                                                                                                                                                                                                                                               |
      | sku         | pim_catalog_identifier   |                                                                                                                                                                                                                                                                   |
      | test_number | pim_catalog_table        | [{"id": "1_d39d3c48-46e6-4744-8196-56e08563fd46", "code": "1", "data_type": "select"}, {"id": "2_d39d3c48-46e6-4744-8196-56e08563fd47", "code": "2", "data_type": "number"}, {"id": "3_d39d3c48-46e6-4744-8196-56e08563fd48", "code":"3", "data_type":"boolean"}] |
    And the following select options:
      | attribute_code | column_code | options                          |
      | test_number    | 1           | [{"code": "11"}, {"code": "12"}] |
    When a product is created with values:
      | attribute   | json_data                         |
      | test_number | [{"1": "11", "2": 20, "3": true}] |
    Then no product violation is raised

  Scenario: Providing a table with too many rows should raise an error
    When a product is created with too many rows
    Then the error "You have reached the maximum number of rows in your table (100)." is raised

  Scenario: Providing a product with too many cells should raise an error
    Given the following attributes:
      | code      | type              | table_configuration                                                                                                                                                                              |
      | packaging | pim_catalog_table | [{"id": "parcel_f6492fb4-d815-4d30-a912-8db321a3e38a", "code": "parcel", "data_type": "select"}, {"id": "length_f967d82a-b54c-41da-959e-1fa43124afee", "code": "length", "data_type": "number"}] |
    When a product is created with too many cells
    Then the error "You have reached the maximum number of table cells in your product (8002 cells, only 8000 allowed)." is raised
