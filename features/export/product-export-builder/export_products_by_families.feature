@javascript
Feature: Export products according to their families
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their families

  Background:
    Given an "footwear" catalog configuration
    And the following family:
      | code       | requirements-mobile |
      | rangers    | sku, name           |
      | boots      | sku, name           |
      | heels      | sku, name           |
      | foo        | sku, name           |
      | bar        | sku, name           |
      | baz        | sku, name           |
      | cloud      | sku, name           |
      | bee        | sku, name           |
      | dog        | sku, name           |
      | cat        | sku, name           |
      | area       | sku, name           |
      | bath       | sku, name           |
      | beer       | sku, name           |
      | bear       | sku, name           |
      | bomb       | sku, name           |
      | ball       | sku, name           |
      | head       | sku, name           |
      | ham        | sku, name           |
      | item       | sku, name           |
      | jean       | sku, name           |
      | snake      | sku, name           |
      | star       | sku, name           |
    And the following products:
      | sku     | family  | categories        | name-en_US       |
      | SNKRS-1 | rangers | summer_collection | Black rangers    |
      | SNKRS-2 | rangers | summer_collection | Black rangers    |
      | SNKRS-3 | heels   | summer_collection | Black high heels |
      | SNKRS-4 | heels   | summer_collection | Black high heels |
      | SNKRS-5 | boots   | summer_collection | Black boots      |
      | SNKRS-6 | boots   | summer_collection | Black boots      |
    And I am logged in as "Julia"

  Scenario: Export only products in boots family
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                            |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "mobile"}, "data": [{"field": "family.code", "operator": "IN", "value": ["boots"]}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    SNKRS-5;summer_collection;;;1;boots;;;;"Black boots";;;;;;;
    SNKRS-6;summer_collection;;;1;boots;;;;"Black boots";;;;;;;
    """

  Scenario: Export only products in boots and high heels family
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "mobile"}, "data": [{"field": "family.code", "operator": "IN", "value": ["boots", "heels"]}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;sole_color;sole_fabric;top_view;weather_conditions
    SNKRS-3;summer_collection;;;1;heels;;;;;"Black high heels";;;;;;;;;
    SNKRS-4;summer_collection;;;1;heels;;;;;"Black high heels";;;;;;;;;
    SNKRS-5;summer_collection;;;1;boots;;;;;"Black boots";;;;;;;;;
    SNKRS-6;summer_collection;;;1;boots;;;;;"Black boots";;;;;;;;;
    """

  Scenario: Export products no matters their families
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;sole_color;sole_fabric;top_view;weather_conditions
    SNKRS-1;summer_collection;;;1;rangers;;;;;"Black rangers";;;;;;;;;
    SNKRS-2;summer_collection;;;1;rangers;;;;;"Black rangers";;;;;;;;;
    SNKRS-3;summer_collection;;;1;heels;;;;;"Black high heels";;;;;;;;;
    SNKRS-4;summer_collection;;;1;heels;;;;;"Black high heels";;;;;;;;;
    SNKRS-5;summer_collection;;;1;boots;;;;;"Black boots";;;;;;;;;
    SNKRS-6;summer_collection;;;1;boots;;;;;"Black boots";;;;;;;;;
    """

  @jira https://akeneo.atlassian.net/browse/PIM-5952
  Scenario: Display default messages when no family are selected
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    Then the export content field "family.code" should contain "No condition on families"
    When I am on the "csv_footwear_product_export" export job page
    And I visit the "Content" tab
    Then the export content field "family.code" should contain "No condition on families"

  @jira https://akeneo.atlassian.net/browse/PIM-6162
  Scenario: View families already selected
    Given I am on the "csv_footwear_product_export" export job edit page
    When I visit the "Content" tab
    And I filter by "family.code" with operator "" and value "rangers,star,snake"
    When I press "Save"
    Then I should see the text "The export has been successfully updated"
    And I should see the text "[rangers] [star] [snake]"
