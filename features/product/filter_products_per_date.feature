@javascript
Feature: Filter products per date
  In order to filter products in the catalog per date
  As a user
  I need to be able to filter products per date

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | library   |
    And the following attributes:
      | type | code    | label-en_US | group | unique | useable_as_grid_column | useable_as_grid_filter | translatable | scopable | allowed_extensions | date_type |
      | date | release | Release     |       | 0      | 1                      | 1                      | 0            | 0        |                    | date      |
    And the following products:
      | sku   | family  | enabled | release    |
      | book1 | library | no      | 2008-02-10 |
      | book2 | library | no      | 2008-02-25 |
      | book3 | library | no      | 2008-01-26 |
      | book4 | library | no      | 2008-03-12 |
    And I am logged in as "admin"

  Scenario: Successfully filter products by date
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Release
    And the grid should contain 4 elements
    And I should see products book1, book2, book3 and book4
    And I should be able to use the following filters:
      | filter  | value                | result          |
      | Release | more than 2008-02-20 | book2 and book4 |
      | Release | less than 2008-02-01 | book3           |
