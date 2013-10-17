@javascript
Feature: Sort attributes
  In order to sort attributes in the catalog
  As a user
  I need to be able to sort attributes by several columns in the catalog

  Background:
    Given there is no attribute
    And the following attribute groups:
      | code      | label     |
      | general   | General   |
      | marketing | Marketing |
    And the following attributes:
      | code        | label       | type                   | scopable | localizable | group     |
      | sku         | Sku         | pim_catalog_identifier | no       | no          | General   |
      | name        | Name        | pim_catalog_text       | no       | yes         | General   |
      | short_descr | Short descr | pim_catalog_textarea   | yes      | yes         | Marketing |
      | count       | Count       | pim_catalog_number     | no       | no          | General   |
    And I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the attributes page
    Then the rows should be sortable by code, label, scopable, localizable and group
    And the rows should be sorted ascending by code
    And I should see sorted attributes count, name, short_descr and sku

  Scenario: Successfully sort attributes by code
    Given I am on the attributes page
    When I sort by "code" value ascending
    Then I should see sorted attributes count, name, short_descr and sku
    When I sort by "code" value descending
    Then I should see sorted attributes sku, short_desc, name and count

  Scenario: Successfully sort attributes by label
    Given I am on the attributes page
    When I sort by "code" value ascending
    Then I should see sorted attributes count, name, short_descr and sku
    When I sort by "code" value descending
    Then I should see sorted attributes sku, short_desc, name and count

  Scenario: Successfully sort attributes by scopable
    Given I am on the attributes page
    When I sort by "scopable" value ascending
    Then I should see sorted attributes sku, name, count and short_desc
    When I sort by "scopable" value descending
    Then I should see sorted attributes short_desc, sku, name and count

  Scenario: Successfully sort attributes by localizable
    Given I am on the attributes page
    When I sort by "localizable" value ascending
    Then I should see sorted attributes sku, count, name and short_desc
    When I sort by "localizable" value descending
    Then I should see sorted attributes name, short_desc, sku and count

  Scenario: Successfully sort attributes by group
    Given I am on the attributes page
    When I sort by "group" value ascending
    Then I should see sorted attributes sku, name, count and short_desc
    When I sort by "group" value descending
    Then I should see sorted attributes short_desc, sku, name and count
