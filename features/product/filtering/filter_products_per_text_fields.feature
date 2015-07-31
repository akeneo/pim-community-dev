@javascript
Feature: Filter products by text field
  In order to filter products by text attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter products with special characters value for text attribute
    Given the following attribute:
      | label | type | useable_as_grid_filter | localizable |
      | name  | text | yes                    | yes         |
    And the following products:
      | sku      | name-en_US                     |
      | 11026270 | HP LA2206xc + WF722A           |
      | 13605290 | Canon 5D + EF 24-105 F4L IS    |
      | 13378171 | Canon 5D + EF 24-105mm f/4L IS |
      | 13572541 | Canon 5D + EF 24-105 F5L IS    |
    When I am on the products page
    And I display the columns sku, name, family, complete, created and updated
    Then the grid should contain 4 elements
    And I should see products "HP LA2206xc + WF722A", "Canon 5D + EF 24-105 F4L IS", "Canon 5D + EF 24-105mm f/4L IS" and "Canon 5D + EF 24-105 F5L IS"
    And I should be able to use the following filters:
      | filter | value                            | result                                                                                      |
      | name   | HP LA2206xc + WF                 | HP LA2206xc + WF722A                                                                        |
      | name   | Canon 5D + EF 24-105             | Canon 5D + EF 24-105 F4L IS, Canon 5D + EF 24-105mm f/4L IS and Canon 5D + EF 24-105 F5L IS |
      | name   | starts with 5D + EF 24-105 F     |                                                                                             |
      | name   | starts with HP                   | HP LA2206xc + WF722A                                                                        |
      | name   | ends with Canon                  |                                                                                             |
      | name   | ends with IS                     | Canon 5D + EF 24-105 F4L IS, Canon 5D + EF 24-105mm f/4L IS and Canon 5D + EF 24-105 F5L IS |
      | name   | ends with is                     | Canon 5D + EF 24-105 F4L IS, Canon 5D + EF 24-105mm f/4L IS and Canon 5D + EF 24-105 F5L IS |
      | name   | does not contain Canon           | HP LA2206xc + WF722A                                                                        |
      | name   | is equal to Canon 5D + EF 24-105 |                                                                                             |
      | name   | f/4L                             | Canon 5D + EF 24-105mm f/4L IS                                                              |

  Scenario: Successfully filter products by empty value for text and textarea attributes
    Given the following attributes:
      | label       | type     | localizable | scopable | useable_as_grid_filter |
      | name        | text     | no          | no       | yes                    |
      | description | textarea | no          | no       | yes                    |
    And the following products:
      | sku    | name     | description      |
      | postit | MyPostit |                  |
      | book   |          |                  |
      | mug    |          | MyMugDescription |
    And the "postit" product has the "description" attribute
    And the "book" product has the "name" attribute
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter      | value | result          |
      | name        | empty | book and mug    |
      | description | empty | postit and book |

  Scenario: Successfully filter products by empty value for localizable text attribute
    Given the following attributes:
      | label | type | localizable | scopable | useable_as_grid_filter |
      | name  | text | yes         | no       | yes                    |
    And the following products:
      | sku    | name-en_US | name-fr_FR |
      | postit | MyPostit   | MonPostit  |
      | book   |            | MonLivre   |
      | mug    |            |            |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result       |
      | name   | empty | book and mug |

  Scenario: Successfully filter products by empty value for scopable text attribute
    Given the following attributes:
      | label | type | localizable | scopable | useable_as_grid_filter |
      | name  | text | no          | yes      | yes                    |
    And the following products:
      | sku    | name-ecommerce | name-mobile |
      | postit | MyPostit       | MyPostit    |
      | book   |                | MyBook      |
      | mug    |                |             |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result       |
      | name   | empty | book and mug |

  Scenario: Successfully filter products by empty value for scopable and localizable text attribute
    Given I add the "english" locale to the "mobile" channel
    And the following attributes:
      | label | type | localizable | scopable | useable_as_grid_filter |
      | name  | text | yes         | yes      | yes                    |
    And the following products:
      | sku    | name-en_US-ecommerce | name-en_US-mobile | name-fr_FR-ecommerce | name-fr_FR-mobile |
      | postit | MyPostit             | MyPostit          | MonPostit            | MonPostit         |
      | book   |                      | MyBook            | MonLivre             | MonLivre          |
      | mug    |                      |                   |                      |                   |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    When I show the filter "name"
    And I should be able to use the following filters:
      | filter | value | result       |
      | name   | empty | book and mug |
