@javascript @published-product-feature-enabled
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

  Scenario: Successfully publish all products
    And I am logged in as "Julia"
    And I am on the products grid
    And I select rows unionjack and jackadi
    And I press the "Bulk actions" button
    When I choose the "Publish" operation
    And I move on to the next step
    Then I should see the text "The 2 selected products will be published"
    And I should see the text "Confirm"
