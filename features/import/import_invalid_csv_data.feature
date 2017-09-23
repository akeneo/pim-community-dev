@javascript
Feature: Handle import of invalid CSV data
  In order to ease the correction of an invalid CSV file import
  As a product manager
  I need to be able to download a CSV file containing all invalid data of an import

  Background:
    Given the "footwear" catalog configuration

  Scenario: From an association type CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;label-en_US
      X_SELL;Cross sells
      UPSELL;Upsells
      SUBSTITUTION;Substitutions
      SUPER PACK;Super pack
      """
    And the following job "csv_footwear_association_type_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_association_type_import" export job page
    And I launch the "csv_footwear_association_type_import" import job
    And I wait for the "csv_footwear_association_type_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_association_type_import" should contain:
      """
      code;label-en_US
      SUPER PACK;Super pack
      """

  Scenario: From an attribute CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order;label-fr_FR;max_characters;number_min;number_max;decimals_allowed;negative_allowed;max_file_size
      pim_catalog_identifier;sku;SKU;info;1;1;;;;;0;0;1;;;;;;;
      pim_catalog_text;name;Name;info;0;1;;;;;1;0;2;;;;;;;
      pim_catalog_simpleselect;manufacturer;Manufacturer;NICE_GROUP;0;1;;;;;0;0;3;;;;;;;
      pim_catalog_multiselect;weather_conditions;"Weather conditions";info;0;1;;;;;0;0;4;;;;;;;
      pim_catalog_textarea;description;Description;info;0;1;;;;;1;1;5;;1000;;;;;
      pim_catalog_text;comment;Comment;other;0;1;;;;;0;0;7;;255;;;;;
      pim_catalog_price_collection;price;Price;NOPE;0;1;;;;;0;0;1;;;1;200;1;;
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_attribute_import" export job page
    And I launch the "csv_footwear_attribute_import" import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_attribute_import" should contain:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order;label-fr_FR;max_characters;number_min;number_max;decimals_allowed;negative_allowed;max_file_size
      pim_catalog_simpleselect;manufacturer;Manufacturer;NICE_GROUP;0;1;;;;;0;0;3;;;;;;;
      pim_catalog_price_collection;price;Price;NOPE;0;1;;;;;0;0;1;;;1;200;1;;
      """

  Scenario: From an attribute option CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      attribute;code;label-en_US;sort_order
      manufacturer;Converse;Converse;1
      manufacturer;TimberLand;TimberLand;2
      invalid code;Nike;Nike;3
      manufacturer;Caterpillar;Caterpillar;4
      weather_conditions;snowy;Snowy;5
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_option_import" export job page
    And I launch the "csv_footwear_option_import" import job
    And I wait for the "csv_footwear_option_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_option_import" should contain:
      """
      attribute;code;label-en_US;sort_order
      invalid code;Nike;Nike;3
      """

  Scenario: From a category CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;label-en_US;parent
      2014_collection;2014 collection;
      invalid code;Summer collection;2014_collection
      winter_collection;Winter collection;2014_collection
      winter_boots;Winter boots;winter_collection
      invalid code 2;Sandals;summer_collection
      """
    And the following job "csv_footwear_category_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_category_import" export job page
    And I launch the "csv_footwear_category_import" import job
    And I wait for the "csv_footwear_category_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_category_import" should contain:
      """
      code;label-en_US;parent
      invalid code;Summer collection;2014_collection
      invalid code 2;Sandals;summer_collection
      """

  Scenario: From a family CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;attributes
      a_family_1;name,description,color
      a_family_2;name,description,number_in_stock
      a_family_3;name,description,size
      a_family_4;name,description,top_view
      a_family_5;name,description,WATERPROOF
      a_family_6;name,description,heel_color
      a_family_7;name,description,weight
      a_family_8;name,description,BULLETPROOF
      a_family_9;name,description,destocking_date
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_family_import" export job page
    And I launch the "csv_footwear_family_import" import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_family_import" should contain:
      """
      code;attributes
      a_family_5;name,description,WATERPROOF
      a_family_8;name,description,BULLETPROOF
      """

  Scenario: From a group CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;type;label-en_US
      similar_boots;RELATED;Similar boots
      invalid code;RELATED;Invalid data
      """
    And the following job "csv_footwear_group_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_group_import" export job page
    And I launch the "csv_footwear_group_import" import job
    And I wait for the "csv_footwear_group_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_group_import" should contain:
      """
      code;type;label-en_US
      invalid code;RELATED;Invalid data
      """

  Scenario: From a product CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-002;sneakers
      SKU-003;sneakers
      SKU-004;sneakers
      SKU-005;boots
      SKU-006;boots
      SKU-007;sneakers
      SKU-008;OTHER_FAMILY
      SKU-009;sneakers
      SKU-010;boots
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_import" export job page
    And I launch the "csv_footwear_product_import" import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_product_import" should contain:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-008;OTHER_FAMILY
      """
