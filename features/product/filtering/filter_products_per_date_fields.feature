@javascript
Feature: Filter products by date field
  In order to filter products by date attributes in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully filter products by empty value for date attribute
    Given the following attributes:
      | label   | type | localizable | scopable | useable as grid filter |
      | release | date | no          | no       | yes                    |
    And the following products:
      | sku    |
      | postit |
      | book   |
      | mug    |
    And the following product values:
      | product| attribute | value      |
      | book   | release   |            |
      | postit | release   | 2014-05-01 |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | value | result       |
      | release | empty | book and mug |
