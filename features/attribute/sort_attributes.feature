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

  Scenario: Successfully sort attributes
    Given I am on the attributes page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, scopable, localizable and group
