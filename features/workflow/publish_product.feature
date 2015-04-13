Feature: Publish a product
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully publish a product
    Given the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And I edit the "my-jacket" product
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product my-jacket

  Scenario: Be able to edit the working copy of a publish product I can edit
    And the following published product:
      | sku       | family  | categories | name-en_US |
      | my-jacket | jackets | jackets    | Jacket1    |
    And I am logged in as "Julia"
    And I am on the "my-jacket" published show page
    Then I should see "Edit working copy"

  Scenario: Not be able to edit the working copy of a publish product I can't edit
    And the following published product:
      | sku    | family | categories | name-en_US |
      | my-tee | tees   | tshirts    | Tee1       |
    And I am logged in as "Julia"
    And I am on the "my-tee" published show page
    Then I should not see "Edit working copy"

  @javascript
  Scenario: Successfully publish a product containing attributes
    Given the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
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
    And the following family:
      | code | label-en_US | attributes                                               |
      | baz  | Baz         | sku, release, available, max_length, popularity, customs |
    And the following product values:
      | product   | attribute  | value         | scope     |
      | my-jacket | release    | 2013-02-02    |           |
      | my-jacket | available  | 2013-02-02    | ecommerce |
      | my-jacket | max_length | 25 CENTIMETER | ecommerce |
      | my-jacket | popularity | 9             | ecommerce |
      | my-jacket | customs    | 100 EUR       | ecommerce |
    And I edit the "my-jacket" product
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product my-jacket
    And I am on the "my-jacket" published show page
    And I should see "Release"
    And I should see "February 02, 2013"
    And I should see "Available"
    And I should see "Length"
    And I should see "25.0000 CENTIMETER"
    And I should see "Popularity"
    And I should see "9"
    And I should see "Customs"
    Then I should see "100.00 EUR"
    Given the following product values:
      | product   | attribute  | value         | scope     |
      | my-jacket | release    | 2014-03-25    |           |
    And I edit the "my-jacket" product
    When I press the "Publish" button
    And I confirm the publishing
    And I am on the published index page
    Then the grid should contain 1 elements
    And I should see product my-jacket
    And I am on the "my-jacket" published show page
    And I should see "March 25, 2014"
