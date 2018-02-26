@javascript
Feature: Export product models according to text attribute filter
  In order to export specific product models
  As a product manager
  I need to be able to export the product models according to text attribute values

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Export product models in csv using the export builder
    When I am on the "csv_product_model_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Model name
    And I switch the locale from "name" filter to "en_US"
    And I filter by "name" with operator "Contains" and value "Heritage"
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_product_model_export" export job page
    And I launch the export job
    And I wait for the "csv_product_model_export" job to finish
    Then exported file of "csv_product_model_export" should contain:
    """
    code;family_variant;parent;categories;brand;care_instructions;collection;description-de_DE-ecommerce;erp_name-de_DE;image;keywords-de_DE;material;meta_description-de_DE;meta_title-de_DE;name-de_DE;notice;price-EUR;price-USD;supplier;wash_temperature;weight;weight-unit
    amor;clothing_colorsize;;master_men_blazers,supplier_zaro;;;summer_2016;;;;;;;;;;999.00;;zaro;800;;
    hermes;clothing_colorsize;;master_men_blazers,supplier_mongo;;;summer_2016;;;;;;;;;;799.00;;mongo;800;;
    """
