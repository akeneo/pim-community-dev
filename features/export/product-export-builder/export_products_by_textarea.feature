@javascript
Feature: Export products according to textarea attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to text attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku,name            | sku,name   |
    And the following products:
      | sku      | enabled | family  | categories        | description-en_US-mobile |
      | SNKRS-1B | 1       | rangers | summer_collection | Awesome                  |
      | SNKRS-1R | 1       | rangers | summer_collection | Awesome description      |
      | SNKRS-1N | 1       | rangers | summer_collection |                          |
    And I am logged in as "Julia"

  @skip
  Scenario: Export products by filtering on textarea values without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                     |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "description", "operator": "=", "value": "Awesome"}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    """

  @skip
  Scenario: Export products by textarea values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    And I filter by "description" with operator "Is equal to" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    """

  Scenario: Export products by textarea values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    And I filter by "description" with operator "Starts with" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;Awesome;
    SNKRS-1R;summer_collection;1;rangers;;Awesome description;
    """

  Scenario: Export products by textarea values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    And I filter by "description" with operator "Contains" and value "Awesome"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;Awesome;
    SNKRS-1R;summer_collection;1;rangers;;Awesome description;
    """

  @skip
  Scenario: Export products by textarea values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    And I filter by "description" with operator "Does not contain" and value "description"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile
    SNKRS-1B;summer_collection;1;rangers;;Awesome
    """

  @skip
  Scenario: Export products by textarea values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    Then I filter by "description" with operator "Is empty" and value ""
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile
    SNKRS-1N;summer_collection;1;rangers;;
    """

  Scenario: Toggle text input when operator doesn't need it
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Description
    When I filter by "description" with operator "Contains" and value "Ranger"
    Then I should see the input filter for "description"
    When I filter by "description" with operator "Is empty" and value ""
    Then I should not see the input filter for "description"
    When I filter by "description" with operator "Is not empty" and value ""
    Then I should not see the input filter for "description"
