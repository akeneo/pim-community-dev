@javascript
Feature: Export products according to text attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to text attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku                 | comment    |
    And the following products:
      | sku      | enabled | family  | categories        | comment         | name-en_US |
      | SNKRS-1B | 1       | rangers | summer_collection | Awesome         |            |
      | SNKRS-1R | 1       | rangers | summer_collection | Awesome product |            |
      | SNKRS-1N | 1       | rangers | summer_collection |                 |            |
      | SNKRS-1Z | 1       | rangers | summer_collection | This is nice    | Ranger 1Z  |
    And I am logged in as "Julia"

  Scenario: Export products by filtering on text values without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filters | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "comment", "operator": "=", "value": "Awesome"}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    """

  Scenario: Export products by text values using the UI
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Comment
    And I filter by "comment" with operator "Is equal to" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    """

  Scenario: Export products by text values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Comment
    And I filter by "comment" with operator "Contains" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    SNKRS-1R;summer_collection;1;rangers;;Awesome product
    """

  Scenario: Export products by text values using the UI
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Comment
    And I filter by "comment" with operator "Does not contain" and value "product"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;Awesome;
    SNKRS-1Z;summer_collection;1;rangers;;This is nice;Ranger 1Z
    """

  Scenario: Export products by text values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Comment
    And I filter by "comment" with operator "Starts with" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    SNKRS-1R;summer_collection;1;rangers;;Awesome product
    """

  Scenario: Export products by text values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Comment
    Then I filter by "comment" with operator "Is empty" and value ""
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment
    SNKRS-1N;summer_collection;1;rangers;;
    """

  Scenario: Export products by localizable text values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Name
    And I switch the locale from "name" filter to "en_US"
    Then I filter by "name" with operator "Contains" and value "Ranger"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    When I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;comment;name-en_US
    SNKRS-1Z;summer_collection;1;rangers;;This is nice;Ranger 1Z
    """

  Scenario: Toggle text input when operator doesn't need it
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Name
    When I filter by "name" with operator "Contains" and value "Ranger"
    Then I should see the input filter for "name"
    When I filter by "name" with operator "Is empty" and value ""
    Then I should not see the input filter for "name"
    When I filter by "name" with operator "Is not empty" and value ""
    Then I should not see the input filter for "name"
