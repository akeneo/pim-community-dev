@acceptance-back
Feature: Enrich a table attribute value
  In order to enrich my products
  As a product manager
  I need to be able to see validation errors when enriching a table

  Background:
    Given an authenticated user
    And the following attributes:
      | code        | type                     | table_configuration                                                                       |
      | sku         | pim_catalog_identifier   |                                                                                           |
      | nutrition   | pim_catalog_table        | [{"code": "ingredient", "data_type": "text"},{"code": "quantity", "data_type": "number"}] |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"

  Scenario: Providing a table with a non existing column should raise an error
    When a product is created with values:
      | attribute   | json_data                                                                    |
      | nutrition   | [{"ingredient": "sugar", "quantity": "20", "non_existing_column": "foobar"}] |
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
