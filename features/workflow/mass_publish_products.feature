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

  Scenario: Only publish products on which user is the owner
    Given I am logged in as "Julia"
    And I am on the products grid
    When I select rows unionjack, jackadi and teafortwo
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Publish" operation
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    Then I should see the text "You're not the owner of the product, you can't publish it"
    And I should see the text "skipped products 1"
    When I am on the published products grid
    Then the grid should contain 2 elements

  Scenario: Publish nothing if the user is the owner of no product
    And I am logged in as "Mary"
    And I am on the products grid
    And I select rows unionjack, jackadi and teafortwo
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Publish" operation
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    Then I should see the text "You're not the owner of the product, you can't publish it"
    And I should see the text "skipped products 3"
    When I am on the published products grid
    Then the grid should contain 0 elements
