@javascript
Feature: Export products according to a locale policy
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a given locale

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code     | type     | localizable | label    | available_locales |
      | name     | textarea | yes         | Name     | all               |
      | baguette | text     | yes         | Baguette | fr_FR             |
    And the following family:
      | code      | requirements-ecommerce |
      | localized | sku, name              |
    And the following products:
      | sku        | categories | family    | name-fr_FR | name-en_US | baguette-fr_FR |
      | french     | default    | localized | French     |            | Yes            |
      | english    | default    | localized |            | English    | Yes            |
      | complete   | default    | localized | Complete   | Complete   | Yes            |
      | empty      | default    | localized |            |            | Yes            |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"

  @ce
  Scenario: Export only the product values from the selected locale
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["fr_FR"], "scope": "ecommerce"}, "data": {}} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;baguette-fr_FR;categories;enabled;family;groups;name-fr_FR
      english;Yes;default;1;localized;;
      complete;Yes;default;1;localized;;Complete
      """

  @ce
  Scenario: Export only the product values from locale specific attributes
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["en_US"], "scope": "ecommerce"}, "data": {}} |
    When I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US
      french;default;1;localized;;
      english;default;1;localized;;English
      complete;default;1;localized;;Complete
      """

  @ce
  Scenario: Remove the locales from the channel after we set the export configuration
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["fr_FR", "en_US"], "scope": "ecommerce"}, "data": {}} |
    When I set the "English (United States)" locale to the "ecommerce" channel
    And I am on the "csv_product_export" export job page
    And I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US
      french;default;1;localized;;
      english;default;1;localized;;English
      complete;default;1;localized;;Complete
      """

  @ce
  Scenario: Selecting a channel from the export profile updates the locale choices
    Given the following job "csv_product_export" configuration:
      | filters | {"structure": {"locales": ["fr_FR"], "scope": "ecommerce"}, "data": {}} |
    And I am on the "csv_product_export" export job edit page
    When I visit the "Content" tab
    Then I should see the text "fr_FR"
    When I fill in the following information:
      | Channel | Mobile |
    Then I should see the text "fr_FR en_US"
