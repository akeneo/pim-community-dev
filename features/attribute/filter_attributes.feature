@javascript
Feature: Filter attributes
  In order to filter product attributes in the catalog
  As a user
  I need to be able to filter attributes in the catalog

  Background:
    Given there is no attribute
    And the following attribute groups:
      | name      |
      | General   |
      | Marketing |
    And the following attributes:
      | code        | label       | type                   | scopable | localizable | group     |
      | sku         | Sku         | pim_catalog_identifier | no       | no          | General   |
      | name        | Name        | pim_catalog_text       | no       | yes         | General   |
      | short_descr | Short descr | pim_catalog_textarea   | yes      | yes         | Marketing |
      | long_descr  | Long descr  | pim_catalog_textarea   | yes      | yes         | Marketing |
      | count       | Count       | pim_catalog_number     | no       | no          | General   |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the attributes page
    Then I should see the filters Code, Label, Type, Scopable, Localizable and Group
    And the grid should contain 5 elements
    And I should see attributes sku, name, short_descr, long_descr and count

  Scenario: Successfully filter by code
    Given I am on the attributes page
    When I filter by "Code" with value "o"
    Then the grid should contain 3 elements
    And I should see attributes short_descr, long_descr and count
    And I should not see attributes sku and name

  Scenario: Successfully filter by label
    Given I am on the attributes page
    When I filter by "Label" with value "descr"
    Then the grid should contain 2 elements
    And I should see attributes short_descr and long_descr
    And I should not see attributes sku, name and count

  Scenario: Successfully filter by a type
    Given I am on the attributes page
    When I filter by "Type" with value "Text Area"
    Then the grid should contain 2 elements
    And I should see attributes short_descr and long_descr
    And I should not see attributes sku, name and count

  Scenario: Successfully filter by scopable "yes"
    Given I am on the attributes page
    When I filter by "Scopable" with value "yes"
    Then the grid should contain 2 elements
    And I should see attributes short_descr and long_descr
    And I should not see attributes sku, name and count

  Scenario: Successfully filter by scopable "no"
    Given I am on the attributes page
    When I filter by "Scopable" with value "no"
    Then the grid should contain 3 elements
    And I should see attributes sku, name and count
    And I should not see attributes short_descr and long_descr

  Scenario: Successfully filter by localizable "yes"
    Given I am on the attributes page
    When I filter by "Localizable" with value "yes"
    Then the grid should contain 3 elements
    And I should see attributes name, short_descr and long_descr
    And I should not see attributes sku and count

  Scenario: Successfully filter by localizable "no"
    Given I am on the attributes page
    When I filter by "Localizable" with value "no"
    Then the grid should contain 2 elements
    And I should see attributes sku and count
    And I should not see attributes name, short_descr and long_descr

  @skip
  Scenario: Successfully filter by group
    Given I am on the attributes page
    When I filter by "Group" with value "General"
    Then the grid should contain 3 elements
    And I should see attributes sku, name and count
    And I should not see attributes short_descr and long_descr

  @skip
  Scenario: Successfully filter by only one group
    Given I am on the attributes page
    And I filter by "Group" with value "General"
    When I filter by "Group" with value "Master"
    Then the grid should contain 2 elements
    And I should see attributes short_descr and long_descr
    And I should not see attributes sku, name and count
