@javascript
Feature: Publish many products at once
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "clothing" catalog configuration
    And the following attributes:
      | code      | label-en_US | type | scopable | unique | date_min   | date_max   | group |
      | release   | Release     | date | no       | yes    | 2013-01-01 | 2015-12-12 | info  |
      | available | Available   | date | yes      | no     | 2013-01-01 | 2015-12-12 | info  |
    And the following attributes:
      | code       | label-en_US | type   | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | max_length | Length      | metric | yes      | Length        | METER               | no               | no               |            |            | info  |
    And the following attributes:
      | code       | label-en_US | type   | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group |
      | popularity | Popularity  | number | yes      | no     | no               | no               | 1          | 10         | info  |
    And the following attributes:
      | code    | label-en_US | type   | scopable | negative_allowed | decimals_allowed | number_min | number_max | group |
      | customs | Customs     | prices | yes      |                  | yes              | 10         | 100        | info  |
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |
      | jackadi   | jackets | Jackadi    | jackets    |
      | teafortwo | tees    | My tee     | tees       |

  Scenario: Only publish products on which user is the owner
    Given I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products unionjack, jackadi and teafortwo
    And I choose the "Publish products" operation
    And I move on to the next step
    And I wait for the "publish" mass-edit job to finish
    Then I should see "You're not the owner of the product, you can't publish it"
    And I should see "skipped products 1"
    When I am on the published index page
    Then the grid should contain 2 elements

  Scenario: Publish nothing if the user is the owner of no product
    And I am logged in as "Mary"
    And I am on the products page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Publish products" operation
    And I move on to the next step
    And I wait for the "publish" mass-edit job to finish
    Then I should see "You're not the owner of the product, you can't publish it"
    And I should see "skipped products 3"
    When I am on the published index page
    Then the grid should contain 0 elements
