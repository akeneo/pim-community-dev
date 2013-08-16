@javascript
Feature: Sort attributes
  In order to sort attributes in the catalog
  As a user
  I need to be able to sort attributes by several columns in the catalog

  Background:
    Given there is no attribute
    And the following attribute groups:
      | name      |
      | General   |
      | Marketing |
    And the following attributes:
      | code        | label       | type                   | scopable | localizable | group     |
      | sku         | Sku         | pim_product_identifier | no       | no          | General   |
      | name        | Name        | pim_product_text       | no       | yes         | General   |
      | short_descr | Short descr | pim_product_textarea   | yes      | yes         | Marketing |
      | count       | Count       | pim_product_number     | no       | no          | General   |
    And I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the attributes page
    Then the datas can be sorted by code, label, scopable, localizable and group
    And the datas are sorted ascending by code
    And I should see sorted attributes count, name, short_descr and sku

  Scenario: Successfully sort attributes by code ascending
    Given I am on the attributes page
    When I sort by "code" value ascending
    Then I should see sorted attributes count, name, short_descr and sku

  Scenario: Successfully sort attributes by code descending
    Given I am on the attributes page
    When I sort by "code" value descending
    Then I should see sorted attributes sku, short_desc, name and count

  Scenario: Successfully sort attributes by label ascending
    Given I am on the attributes page
    When I sort by "code" value ascending
    Then I should see sorted attributes count, name, short_descr and sku

  Scenario: Successfully sort attributes by label descending
    Given I am on the attributes page
    When I sort by "code" value descending
    Then I should see sorted attributes sku, short_desc, name and count

  Scenario: Successfully sort attributes by scopable ascending
    Given I am on the attributes page
    When I sort by "scopable" value ascending
    Then I should see sorted attributes sku, name, count and short_desc

  Scenario: Successfully sort attributes by scopable descending
    Given I am on the attributes page
    When I sort by "scopable" value descending
    Then I should see sorted attributes short_desc, sku, name and count

  Scenario: Successfully sort attributes by localizable ascending
    Given I am on the attributes page
    When I sort by "localizable" value ascending
    Then I should see sorted attributes sku, count, name and short_desc

  Scenario: Successfully sort attributes by localizable descending
    Given I am on the attributes page
    When I sort by "localizable" value descending
    Then I should see sorted attributes name, short_desc, sku and count

  @skip
  Scenario: Successfully sort attributes by group ascending
    Given I am on the attributes page
    When I sort by "group" value ascending
    Then I should see sorted attributes sku, name, count and short_desc

  @skip
  Scenario: Successfully sort attributes by group descending
    Given I am on the attributes page
    When I sort by "group" value descending
    Then I should see sorted attributes short_desc, sku, name and count
