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
    When a product is created with values:
      | attribute | json_data                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | nutrition | [{"ingredient":"ingredient_0","quantity":0},{"ingredient":"ingredient_1","quantity":1},{"ingredient":"ingredient_2","quantity":2},{"ingredient":"ingredient_3","quantity":3},{"ingredient":"ingredient_4","quantity":4},{"ingredient":"ingredient_5","quantity":5},{"ingredient":"ingredient_6","quantity":6},{"ingredient":"ingredient_7","quantity":7},{"ingredient":"ingredient_8","quantity":8},{"ingredient":"ingredient_9","quantity":9},{"ingredient":"ingredient_10","quantity":10},{"ingredient":"ingredient_11","quantity":11},{"ingredient":"ingredient_12","quantity":12},{"ingredient":"ingredient_13","quantity":13},{"ingredient":"ingredient_14","quantity":14},{"ingredient":"ingredient_15","quantity":15},{"ingredient":"ingredient_16","quantity":16},{"ingredient":"ingredient_17","quantity":17},{"ingredient":"ingredient_18","quantity":18},{"ingredient":"ingredient_19","quantity":19},{"ingredient":"ingredient_20","quantity":20},{"ingredient":"ingredient_21","quantity":21},{"ingredient":"ingredient_22","quantity":22},{"ingredient":"ingredient_23","quantity":23},{"ingredient":"ingredient_24","quantity":24},{"ingredient":"ingredient_25","quantity":25},{"ingredient":"ingredient_26","quantity":26},{"ingredient":"ingredient_27","quantity":27},{"ingredient":"ingredient_28","quantity":28},{"ingredient":"ingredient_29","quantity":29},{"ingredient":"ingredient_30","quantity":30},{"ingredient":"ingredient_31","quantity":31},{"ingredient":"ingredient_32","quantity":32},{"ingredient":"ingredient_33","quantity":33},{"ingredient":"ingredient_34","quantity":34},{"ingredient":"ingredient_35","quantity":35},{"ingredient":"ingredient_36","quantity":36},{"ingredient":"ingredient_37","quantity":37},{"ingredient":"ingredient_38","quantity":38},{"ingredient":"ingredient_39","quantity":39},{"ingredient":"ingredient_40","quantity":40},{"ingredient":"ingredient_41","quantity":41},{"ingredient":"ingredient_42","quantity":42},{"ingredient":"ingredient_43","quantity":43},{"ingredient":"ingredient_44","quantity":44},{"ingredient":"ingredient_45","quantity":45},{"ingredient":"ingredient_46","quantity":46},{"ingredient":"ingredient_47","quantity":47},{"ingredient":"ingredient_48","quantity":48},{"ingredient":"ingredient_49","quantity":49},{"ingredient":"ingredient_50","quantity":50},{"ingredient":"ingredient_51","quantity":51},{"ingredient":"ingredient_52","quantity":52},{"ingredient":"ingredient_53","quantity":53},{"ingredient":"ingredient_54","quantity":54},{"ingredient":"ingredient_55","quantity":55},{"ingredient":"ingredient_56","quantity":56},{"ingredient":"ingredient_57","quantity":57},{"ingredient":"ingredient_58","quantity":58},{"ingredient":"ingredient_59","quantity":59},{"ingredient":"ingredient_60","quantity":60},{"ingredient":"ingredient_61","quantity":61},{"ingredient":"ingredient_62","quantity":62},{"ingredient":"ingredient_63","quantity":63},{"ingredient":"ingredient_64","quantity":64},{"ingredient":"ingredient_65","quantity":65},{"ingredient":"ingredient_66","quantity":66},{"ingredient":"ingredient_67","quantity":67},{"ingredient":"ingredient_68","quantity":68},{"ingredient":"ingredient_69","quantity":69},{"ingredient":"ingredient_70","quantity":70},{"ingredient":"ingredient_71","quantity":71},{"ingredient":"ingredient_72","quantity":72},{"ingredient":"ingredient_73","quantity":73},{"ingredient":"ingredient_74","quantity":74},{"ingredient":"ingredient_75","quantity":75},{"ingredient":"ingredient_76","quantity":76},{"ingredient":"ingredient_77","quantity":77},{"ingredient":"ingredient_78","quantity":78},{"ingredient":"ingredient_79","quantity":79},{"ingredient":"ingredient_80","quantity":80},{"ingredient":"ingredient_81","quantity":81},{"ingredient":"ingredient_82","quantity":82},{"ingredient":"ingredient_83","quantity":83},{"ingredient":"ingredient_84","quantity":84},{"ingredient":"ingredient_85","quantity":85},{"ingredient":"ingredient_86","quantity":86},{"ingredient":"ingredient_87","quantity":87},{"ingredient":"ingredient_88","quantity":88},{"ingredient":"ingredient_89","quantity":89},{"ingredient":"ingredient_90","quantity":90},{"ingredient":"ingredient_91","quantity":91},{"ingredient":"ingredient_92","quantity":92},{"ingredient":"ingredient_93","quantity":93},{"ingredient":"ingredient_94","quantity":94},{"ingredient":"ingredient_95","quantity":95},{"ingredient":"ingredient_96","quantity":96},{"ingredient":"ingredient_97","quantity":97},{"ingredient":"ingredient_98","quantity":98},{"ingredient":"ingredient_99","quantity":99},{"ingredient":"ingredient_100","quantity":100}] |
    Then the error "You have reached the maximum number of rows in your table (100)." is raised
