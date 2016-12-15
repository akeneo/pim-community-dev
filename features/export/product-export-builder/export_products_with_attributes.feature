@javascript
Feature: Export products with only selected attributes
  In order to export products with a subset of attributes
  As a product manager
  I need to be able to export only the attributes I need

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code                 | label-en_US          | type         | group  |
      | high_heel_color      | High heel color      | simpleselect | colors |
      | high_heel_color_sole | High heel color sole | simpleselect | colors |
    And the following "high_heel_color" attribute options: Red, Blue
    And the following "high_heel_color_sole" attribute options: Green, Orange
    And the following products:
      | sku    | family | name-en_US | weather_conditions | categories      |
      | BOOT-1 | boots  | The boot 1 |                    | 2014_collection |
      | BOOT-2 | boots  | The boot 2 | dry                | 2014_collection |
    And I am logged in as "Julia"

  Scenario: Export products by selecting only one attribute
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                              |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile", "attributes": ["weather_conditions"]},"data":[]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions
    BOOT-1;;1;boots;;
    BOOT-2;;1;boots;;dry
    """

  Scenario: Export products by selecting only one attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I select the following attributes to export weather_conditions
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions
    BOOT-1;;1;boots;;
    BOOT-2;;1;boots;;dry
    """

  Scenario: Export products by selecting multiple attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I select the following attributes to export weather_conditions and lace_color
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions;lace_color
    BOOT-1;;1;boots;;;
    BOOT-2;;1;boots;;dry;
    """

  Scenario: Export products by selecting multiple attribute using the UI in a specific order
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I select the following attributes to export lace_color and weather_conditions
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contains the following headers:
    """
    sku;categories;enabled;family;groups;lace_color;weather_conditions
    """
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I select the following attributes to export weather_conditions and lace_color
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contains the following headers:
    """
    sku;categories;enabled;family;groups;weather_conditions;lace_color
    """

  Scenario: Export products by selecting no attributes using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I select no attribute to export
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    BOOT-1;;;;1;boots;;;;"The boot 1";;;;;;;
    BOOT-2;;;;1;boots;;;;"The boot 2";;;;;;;dry
    """

  @jira https://akeneo.atlassian.net/browse/PIM-5941
  Scenario: Navigate between export profile tabs
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "sku" with operator "IN" and value "BOOT-1"
    And I press the "Save" button
    And I should not see the text "There are unsaved changes"
    Then I should be on the "Content" tab
    When I visit the "History" tab
    And I press the "Edit" button
    Then I should see the "Save" button
    And I should be on the "History" tab

  @jira https://akeneo.atlassian.net/browse/PIM-5994
  Scenario: Export the attributes only once
    Given the following family:
      | code       | attributes                                 | requirements-mobile                        |
      | high_heels | sku, high_heel_color, high_heel_color_sole | sku, high_heel_color, high_heel_color_sole |
    And the following products:
      | sku    | family     | name-en_US     | high_heel_color | high_heel_color_sole | categories      |
      | HEEL-1 | high_heels | The red heels  | Red             | Green                | 2014_collection |
      | HEEL-2 | high_heels | The blue heels | Blue            | Orange               | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "mobile", "attributes": ["high_heel_color", "high_heel_color_sole"]}, "data": [{"field": "family.code", "operator": "IN", "value": ["high_heels"]}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;high_heel_color;high_heel_color_sole
    HEEL-1;2014_collection;1;high_heels;;Red;Green
    HEEL-2;2014_collection;1;high_heels;;Blue;Orange
    """
