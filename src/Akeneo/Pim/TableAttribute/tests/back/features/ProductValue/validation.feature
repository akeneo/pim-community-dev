@acceptance-back
Feature: Enrich a table attribute value
  In order to enrich my products
  As a product manager
  I need to be able to see validation errors when enriching a table

  Background:
    Given an authenticated user
    And the following attributes:
      | code        | type                     | table_configuration                                                                                                                                                                                                                   |
      | sku         | pim_catalog_identifier   |                                                                                                                                                                                                                                       |
      | nutrition   | pim_catalog_table        | [{"code": "ingredient", "data_type": "select", "labels": {"en_US": "Ingredient"}, "options": [{"code": "sugar", "labels": {"fr_FR": "Sucre"}}, {"code": "salt"}]}, {"code": "quantity", "data_type": "number"}, {"code":"isAllergen", "data_type":"boolean"}, {"code":"comments", "data_type":"text"}] |
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
      | attribute | json_data                                 |
      | nutrition | [{"ingredient": 1, "quantity": "abcdef"}] |
    Then the error 'The "quantity" column expects a numeric, string given' is raised
    And the error 'The "ingredient" column expects a string, integer given' is raised

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
      | code        | type                     | table_configuration                                                                                               |
      | sku         | pim_catalog_identifier   |                                                                                                                   |
      | test_number | pim_catalog_table        | [{"code": "1", "data_type": "select"}, {"code": "2", "data_type": "number"}, {"code":"3", "data_type":"boolean"}] |
    And the following select options:
      | attribute_code | column_code | options                          |
      | test_number    | 1           | [{"code": "11"}, {"code": "12"}] |
    When a product is created with values:
      | attribute   | json_data                         |
      | test_number | [{"1": "11", "2": 20, "3": true}] |
    Then no product violation is raised
