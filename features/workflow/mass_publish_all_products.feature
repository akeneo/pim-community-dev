@javascript
Feature: Publish many products at once
  In order to froze the product data I would use to export
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
    And I am on the products page
    And I select rows unionjack and jackadi
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    And I should see "Confirm"

  @jira https://akeneo.atlassian.net/browse/PIM-3784
  Scenario: Successfully publish all products
    Given I am logged in as "Julia"
    And I am on the products page
    And I select rows unionjack
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Publish products" operation
    And I move on to the next step
    And I wait for the "publish" mass-edit job to finish
    And I am on the published products page
    And I should see product unionjack
    Then the row "unionjack" should contain:
      | column   | value |
      | complete | 20%   |
