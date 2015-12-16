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

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4762
  Scenario: Successfully mass-publish products containing attributes
    Given I am logged in as "Peter"
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
      | my-shoes  | jackets | Shoes      |
    And the following family:
      | code | label-en_US | attributes                                               |
      | baz  | Baz         | sku, release, available, max_length, popularity, customs |
    And the following product values:
      | product   | attribute  | value         | scope  |
      | my-jacket | release    | 2013-02-02    |        |
      | my-shoes  | release    | 2013-02-03    |        |
      | my-jacket | available  | 2013-02-02    | tablet |
      | my-shoes  | available  | 2013-02-03    | tablet |
      | my-jacket | max_length | 60 CENTIMETER | tablet |
      | my-shoes  | max_length | 25 CENTIMETER | tablet |
      | my-jacket | popularity | 9             | tablet |
      | my-shoes  | popularity | 10            | tablet |
      | my-jacket | customs    | 100 EUR       | tablet |
      | my-shoes  | customs    | 50 EUR        | tablet |
    And I am on the products page
    And I mass-edit products my-jacket and my-shoes
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    And I move on to the next step
    And I wait for the "publish" mass-edit job to finish
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should see product my-jacket and my-shoes
    And I am on the "my-jacket" published show page
    And I should see "Release"
    And I should see "February 02, 2013"
    And I should see "Available"
    And I should see "Length"
    And I should see "60.0000 CENTIMETER"
    And I should see "Popularity"
    And I should see "9"
    And I should see "Customs"
    Then I should see "100.00 EUR"
    And I am on the "my-shoes" published show page
    And I should see "Release"
    And I should see "February 03, 2013"
    And I should see "Available"
    And I should see "Length"
    And I should see "25.0000 CENTIMETER"
    And I should see "Popularity"
    And I should see "10"
    And I should see "Customs"
    Then I should see "50.00 EUR"
