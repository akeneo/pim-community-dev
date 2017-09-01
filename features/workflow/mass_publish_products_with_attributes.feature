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

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4762
  Scenario: Successfully mass-publish products containing attributes
    Given I am logged in as "Peter"
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
      | my-shoes  | jackets | Shoes      |
    And the following family:
      | code | label-en_US | attributes                                          |
      | baz  | Baz         | sku,release,available,max_length,popularity,customs |
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
    And I am on the products grid
    And I select rows my-jacket and my-shoes
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Publish" operation
    Then I should see the text "The 2 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 2 elements
    And I should see product my-jacket and my-shoes
    And I am on the "my-jacket" published product show page
    And I should see the text "Release"
    And I should see the text "February 02, 2013"
    And I should see the text "Available"
    And I should see the text "Length"
    And I should see the text "60.0000 CENTIMETER"
    And I should see the text "Popularity"
    And I should see the text "9"
    And I should see the text "Customs"
    Then I should see the text "100.00 EUR"
    And I am on the "my-shoes" published product show page
    And I should see the text "Release"
    And I should see the text "February 03, 2013"
    And I should see the text "Available"
    And I should see the text "Length"
    And I should see the text "25.0000 CENTIMETER"
    And I should see the text "Popularity"
    And I should see the text "10"
    And I should see the text "Customs"
    Then I should see the text "50.00 EUR"
