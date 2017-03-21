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
      | sku      | categories | family    | name-fr_FR | name-en_US |
      | french   | default    | localized | French     |            |
      | english  | default    | localized |            | English    |
      | complete | default    | localized | Complete   | Complete   |
      | empty    | default    | localized |            |            |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    Given the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                        |
      | filters  | {"structure": {"locales": ["fr_FR", "en_US"], "scope": "ecommerce"},"data":[]} |
    And I am logged in as "Julia"

  @ce
  Scenario: Export the products complete from at least one selected locale (default)
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["fr_FR", "en_US"], "scope": "ecommerce"},"data":[{"field":"completeness","operator":"=","value":"100","context":{"locales":["fr_FR","en_US"]}}]} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      french;default;1;localized;;;French
      english;default;1;localized;;English;
      complete;default;1;localized;;Complete;Complete
      """

  @ce
  Scenario: Export the complete products of all selected locales
    Given the following job "csv_product_export" configuration:
      | filters | {"structure":{"locales":["fr_FR","en_US"],"scope":"ecommerce"},"data":[{"field":"completeness","operator":"GREATER OR EQUALS THAN ON ALL LOCALES","value":"100","context":{"locales":["fr_FR","en_US"]}}]} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      complete;default;1;localized;;Complete;Complete
      """

  @ce
  Scenario: Export the incomplete products of all selected locales
    Given the following job "csv_product_export" configuration:
      | filters | {"structure":{"locales":["fr_FR","en_US"],"scope":"ecommerce"},"data":[{"field":"completeness","operator":"LOWER THAN ON ALL LOCALES","value":100,"context":{"locales":["fr_FR","en_US"]}}]} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      empty;default;1;localized;;;
      """

  @ce
  Scenario: Export all products
    Given the following job "csv_product_export" configuration:
      | filters | {"structure":{"locales":["fr_FR","en_US"],"scope":"ecommerce"},"data":[]} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      french;default;1;localized;;;French
      english;default;1;localized;;English;
      complete;default;1;localized;;Complete;Complete
      empty;default;1;localized;;;
      """

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
      sku;categories;enabled;family;groups;name-en_US;name-fr_FR
      french;default;1;localized;;;French
      english;default;1;localized;;English;
      complete;default;1;localized;;Complete;Complete
      empty;default;1;localized;;;
      """
