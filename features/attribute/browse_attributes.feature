@javascript
Feature: Browse attributes
  In order to check wether or not an attribute is available in the catalog
  As a user
  I need to be able to see attributes in the catalog

  Background:
    Given there is no attribute
    And the following attribute groups:
      | name      |
      | General   |
      | Marketing |
    And the following attributes:
      | code        | label       | type                   | scopable | localizable | group     |
      | sku         | Sku         | pim_catalog_identifier | false    | false       | General   |
      | name        | Name        | pim_catalog_text       | false    | true        | General   |
      | short_descr | Short descr | pim_catalog_textarea   | true     | true        | Marketing |
      | long_descr  | Long descr  | pim_catalog_textarea   | true     | true        | Marketing |
      | count       | Count       | pim_catalog_number     | false    | false       | General   |
    And I am logged in as "admin"

  Scenario: Successfully display attributes
    Given I am on the attributes page
    Then the grid should contain 5 elements
    And I should see attributes sku, name, short_descr, long_descr and count

  Scenario: Successfully display columns
    Given I am on the attributes page
    Then I should see the columns Code, Label, Type, Scopable, Localizable and Group
