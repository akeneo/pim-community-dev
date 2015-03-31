@javascript
Feature: Publish many products at once
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |
      | jackadi   | jackets | Jackadi    | jackets    |
      | teafortwo | tees    | My tee     | tees       |

  Scenario: Successfully publish all products
    And I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    And I should see "Confirm"

  @skip
  Scenario: Successfully publish few products of selected
    And I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Publish products" operation
    Then I should see "You're not the owner of all the selected products. You can't publish the products \"teafortwo\""
    And I should see "Confirm"

  @skip
  Scenario: Forbid to publish if user is not the owner of at least one product
    And I am logged in as "Mary"
    And I am on the products page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Publish products" operation
    Then I should see "You're not the owner of the selected products, you can't publish them"

  @jira https://akeneo.atlassian.net/browse/PIM-3636
  Scenario: Allow to mass publish two products that are associated in two ways (jackadi => unionjack, unionjack => jackadi), I should be able to publish them twice
    Given I am logged in as "Julia"
    And I edit the "unionjack" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "jackadi"
    And I press the "Save working copy" button
    And I edit the "jackadi" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "unionjack"
    And I press the "Save working copy" button
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                               | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["unionjack", "jackadi"]}] | {"publish": true} |
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                               | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["unionjack", "jackadi"]}] | {"publish": true} |
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi

  @jira https://akeneo.atlassian.net/browse/PIM-3636
  Scenario: Allow to mass publish two products that are associated, I should be able to publish them twice
    Given I am logged in as "Peter"
    And I edit the "unionjack" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "jackadi"
    And I press the "Save working copy" button
    And I edit the "teafortwo" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "jackadi"
    And I check the row "unionjack"
    And I press the "Save working copy" button
    And I edit the "jackadi" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "teafortwo"
    And I press the "Save working copy" button
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                               | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["unionjack", "jackadi"]}] | {"publish": true} |
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                               | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["unionjack", "jackadi"]}] | {"publish": true} |
    And I am on the published index page
    Then the grid should contain 2 elements
    And I should see product unionjack and jackadi

  Scenario: Successfully mass-publish products containing attributes
    Given I am logged in as "Peter"
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
      | my-shoes  | jackets | Shoes      |
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
      | my-shoes  | release    | 2013-02-03    |           |
      | my-jacket | available  | 2013-02-02    | ecommerce |
      | my-shoes  | available  | 2013-02-03    | ecommerce |
      | my-jacket | max_length | 60 CENTIMETER | ecommerce |
      | my-shoes  | max_length | 25 CENTIMETER | ecommerce |
      | my-jacket | popularity | 9             | ecommerce |
      | my-shoes  | popularity | 10            | ecommerce |
      | my-jacket | customs    | 100 EUR       | ecommerce |
      | my-shoes  | customs    | 50 EUR        | ecommerce |
    And I am on the products page
    And I mass-edit products my-jacket and my-shoes
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                                | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["my-jacket", "my-shoes"]}] | {"publish": true} |
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

  @jira https://akeneo.atlassian.net/browse/PIM-3784
  Scenario: Successfully publish all products
    Given I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products unionjack
    When I choose the "Publish products" operation
    When I press the "Next" button
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                    | actions           |
      | publish   | [{"field":"sku", "operator":"IN", "value": ["unionjack"]}] | {"publish": true} |
    And I am on the published index page
    And I should see product unionjack
    Then the row "unionjack" should contain:
      | column   | value |
      | complete | 22%   |
