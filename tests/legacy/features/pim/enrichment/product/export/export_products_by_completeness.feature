@javascript
Feature: Export products according to a completeness policy
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to the completeness

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code | type                 | localizable | label-en_US | group |
      | name | pim_catalog_textarea | 1           | Name        | other |
    And the following family:
      | code      | requirements-ecommerce | attributes |
      | localized | sku,name               | sku,name   |
    And the following products:
      | uuid                                 | sku      | categories | family    | name-fr_FR | name-en_US |
      | 65981adb-242a-473e-80a5-8e4f2080edf1 | french   | default    | localized | French     |            |
      | 8e43d1d2-96e7-45dd-9120-ca11df22cbd4 | english  | default    | localized |            | English    |
      | 77db32aa-7164-4444-b516-890d587af7eb | complete | default    | localized | Complete   | Complete   |
      | f9316596-dfca-4ebc-9452-f6b81b704045 | empty    | default    | localized |            |            |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    Given the following job "csv_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"}      |
      | filters   | {"structure": {"locales": ["fr_FR", "en_US"], "scope": "ecommerce"},"data":[]} |
      | with_uuid | yes                                                                            |
    And I am logged in as "Julia"

  Scenario: Export products with operator ALL on completeness
    Given I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      uuid;sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      65981adb-242a-473e-80a5-8e4f2080edf1;french;default;1;localized;;;French
      8e43d1d2-96e7-45dd-9120-ca11df22cbd4;english;default;1;localized;;English;
      77db32aa-7164-4444-b516-890d587af7eb;complete;default;1;localized;;Complete;Complete
      f9316596-dfca-4ebc-9452-f6b81b704045;empty;default;1;localized;;;
      """
