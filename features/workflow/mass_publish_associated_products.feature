@javascript
Feature: Publish many products at once
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "clothing" catalog configuration
    And the following attributes:
      | code      | label-en_US | type             | scopable | unique | date_min   | date_max   | group |
      | release   | Release     | pim_catalog_date | 0        | 1      | 2013-01-01 | 2015-12-12 | info  |
      | available | Available   | pim_catalog_date | 1        | 0      | 2013-01-01 | 2015-12-12 | info  |
    And the following attributes:
      | code       | label-en_US | type               | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | max_length | Length      | pim_catalog_metric | 1        | Length        | METER               | 0                | 0                |            |            | info  |
    And the following attributes:
      | code       | label-en_US | type               | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group |
      | popularity | Popularity  | pim_catalog_number | 1        | 0      | 0                | 0                | 1          | 10         | info  |
    And the following attributes:
      | code    | label-en_US | type                         | scopable | negative_allowed | decimals_allowed | number_min | number_max | group |
      | customs | Customs     | pim_catalog_price_collection | 1        |                  | 1                | 10         | 100        | info  |
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |
      | jackadi   | jackets | Jackadi    | jackets    |
      | teafortwo | tees    | My tee     | tees       |

  @jira https://akeneo.atlassian.net/browse/PIM-3636
  Scenario: Allow to mass publish two products that are associated in two ways (jackadi => unionjack, unionjack => jackadi), I should be able to publish them twice
    Given I am logged in as "Julia"
    And I edit the "unionjack" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "jackadi"
    And I save the product
    And I edit the "jackadi" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "unionjack"
    And I save the product
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    When I am on the products grid
    Then the grid should contain 3 elements
    And I should see product unionjack, jackadi and teafortwo

  @jira https://akeneo.atlassian.net/browse/PIM-3636
  Scenario: Allow to mass publish two products that are associated, I should be able to publish them twice
    Given I am logged in as "Peter"
    And I edit the "unionjack" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "jackadi"
    And I save the product
    And I edit the "teafortwo" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "jackadi"
    And I check the row "unionjack"
    And I save the product
    And I edit the "jackadi" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "teafortwo"
    And I save the product
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi

  @jira https://akeneo.atlassian.net/browse/PIM-6024
  Scenario: Succesfully mass publish associated product
    Given I am logged in as "Peter"
    And I edit the "unionjack" product
    When I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I check the row "jackadi"
    And I save the product
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    When I select rows unionjack and jackadi
    And I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the "csv_published_product_quick_export" quick export to finish
    Then exported file of "csv_published_product_quick_export" should contain:
    """
    sku;categories;datasheet;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;enabled;family;gallery;groups;handmade;length;length-unit;main_color;manufacturer;name-de_DE;name-en_US;name-fr_FR;number_in_stock-mobile;PACK-groups;PACK-product_models;PACK-products;price-EUR;price-USD;rating;release_date-mobile;secondary_color;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-product_models;SUBSTITUTION-products;top_view;UPSELL-groups;UPSELL-product_models;UPSELL-products;weather_conditions;X_SELL-groups;X_SELL-product_models;X_SELL-products
    unionjack;jackets;;;;;1;jackets;;;0;;;;;;UnionJack;;;;;;;;;;;;;;;;;;;;;;;jackadi
    jackadi;jackets;;;;;1;jackets;;;0;;;;;;Jackadi;;;;;;;;;;;;;;;;;;;;;;;
    """
