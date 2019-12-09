@javascript
Feature: Filter products by text field
  In order to filter products by text attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully filter products with special characters value for text attribute
    Given the following attribute:
      | label-en_US | type             | useable_as_grid_filter | localizable | group | code |
      | name        | pim_catalog_text | 1                      | 1           | other | name |
    And the following products:
      | sku      | name-en_US                     |
      | 11026270 | HP LA2206xc + WF722A           |
      | 13605290 | Canon 5D + EF 24-105 F4L IS    |
      | 13378171 | Canon 5D + EF 24-105mm f/4L IS |
      | 13572541 | Canon 5D + EF 24-105 F5L IS    |
      | 135-2541 | Canon 5D + EF 25-15 FL         |
    When I am on the products grid
    And I collapse the column
    And I display the columns SKU, Name, Family, Complete, Created at and Updated at
    Then I should see products "HP LA2206xc + WF722A", "Canon 5D + EF 24-105 F4L IS", "Canon 5D + EF 24-105mm f/4L IS" and "Canon 5D + EF 24-105 F5L IS"
    And the grid should contain 5 elements
    And I uncollapse the column
    And I should be able to use the following filters:
      | filter | operator         | value                | result                                                                                      |
      | name   | contains         | HP LA2206xc + WF     | HP LA2206xc + WF722A                                                                        |

  Scenario: Successfully filter products with special characters value for text attribute
    Given the following attribute:
      | label-en_US | type             | useable_as_grid_filter | localizable | group | code |
      | name        | pim_catalog_text | 1                      | 1           | other | name |
    And the following products:
      | sku      | name-en_US                     |
      | 11026270 | HP LA2206xc + WF722A           |
      | 13605290 | Canon 5D + EF 24-105 F4L IS    |
      | 13378171 | Canon 5D + EF 24-105mm f/4L IS |
      | 13572541 | Canon 5D + EF 24-105 F5L IS    |
      | 135-2541 | Canon 5D + EF 25-15 FL         |
    When I am on the products grid
    And I collapse the column
    And I display the columns SKU, Name, Family, Complete, Created at and Updated at
    Then I should see products "HP LA2206xc + WF722A", "Canon 5D + EF 24-105 F4L IS", "Canon 5D + EF 24-105mm f/4L IS" and "Canon 5D + EF 24-105 F5L IS"
    And the grid should contain 5 elements
    And I uncollapse the column
    And I should be able to use the following filters:
      | filter | operator         | value                | result                                                                                      |
      | name   | contains         | HP LA2206xc + WF     | HP LA2206xc + WF722A                                                                        |
      | name   | contains         | Canon 5D + EF 24-105 | Canon 5D + EF 24-105 F4L IS, Canon 5D + EF 24-105mm f/4L IS and Canon 5D + EF 24-105 F5L IS |
      | name   | starts with      | 5D + EF 24-105 F     |                                                                                             |
      | name   | starts with      | HP                   | HP LA2206xc + WF722A                                                                        |
      | name   | does not contain | Canon                | HP LA2206xc + WF722A                                                                        |
      | name   | is equal to      | Canon 5D + EF 24-105 |                                                                                             |
      | name   | contains         | f/4L                 | Canon 5D + EF 24-105mm f/4L IS                                                              |
      | sku    | is equal to      | 135-2541             | 135-2541                                                                                    |
      | sku    | in list          | 135-2541, 13572541   | 135-2541, 13572541                                                                          |

  @critical
  Scenario: Successfully filter products by empty value for scopable text attribute
    Given the following attributes:
      | label-en_US | type             | localizable | scopable | useable_as_grid_filter | group | code |
      | name        | pim_catalog_text | 0           | 1        | 1                      | other | name |
    And the following products:
      | sku    | name-ecommerce | name-mobile |
      | postit | MyPostit       | MyPostit    |
      | book   |                | MyBook      |
      | mug    |                |             |
    And I am on the products grid
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | operator     | value | result |
      | name   | is empty     |       |        |
      | name   | is not empty |       | postit |
