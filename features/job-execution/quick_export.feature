@javascript
Feature: Quick export products
  In order to easily quick export a set of products
  As a product manager
  I need to be able to see the result of a quick export and to download logs and files

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | label-en_US | type               | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed | group | code   |
      | Weight      | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | other | weight |
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | 123 | description-en_US-tablet | weight | weight-unit |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa | Mob                      | 20     | GRAM        |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb | ylette                   | 4      | GRAM        |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |                          |        |             |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |                          |        |             |
    And I am logged in as "Julia"

  Scenario: Go to the job execution page for a "quick export" (by clicking on the notifications) and then check buttons status on the header
    Given I am on the products grid
    And I select rows boots, sneakers, pump
    When I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    When I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And I should see the text "csv_product_grid_context_quick_export"
    And I should see the text "csv product grid context quick export"
    And I should see the secondary action "Download invalid data"
    And I should not see the secondary action "Download read files"
    And I should see the text "Download generated files"
    And I should not see the secondary action "Download generated archive"
    And I should not see the secondary action "Show profile"
