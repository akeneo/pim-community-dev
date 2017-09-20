@javascript
Feature: Export products according to textarea attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to text attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code          | type                 | label-en_US   | group |
      | description_2 | pim_catalog_textarea | Description 2 | other |
      | description_3 | pim_catalog_textarea | Description 3 | other |
      | description_4 | pim_catalog_textarea | Description 4 | other |
      | description_5 | pim_catalog_textarea | Description 5 | other |
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku,name            | sku,name,description,description_2,description_3,description_4,description_5 |
    And the following products:
      | sku      | enabled | family  | categories        | description-en_US-mobile | description_2       | description_3    | description_4 | description_5 |
      | SNKRS-1B | 1       | rangers | summer_collection | Awesome                  | Awesome description | Nice description | Amazing       |               |
      | SNKRS-1R | 1       | rangers | summer_collection | Awesome description      |                     |                  |               | Amazing       |
      | SNKRS-1N | 1       | rangers | summer_collection |                          |                     |                  | description   |               |
    And I am logged in as "Julia"

  Scenario: Export products by textarea values
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I add available attributes Description
    And I add available attributes Description 2
    And I add available attributes Description 3
    And I add available attributes Description 4
    And I add available attributes Description 5
    And I filter by "description" with operator "Is equal to" and value "Awesome"
    And I filter by "description_2" with operator "Starts with" and value "Awesome"
    And I filter by "description_3" with operator "Contains" and value "desc"
    And I filter by "description_4" with operator "Does not contain" and value "description"
    Then I filter by "description_5" with operator "Is empty" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;description-en_US-mobile;description_2;description_3;description_4;description_5;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;Awesome;"Awesome description";"Nice description";Amazing;;
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
