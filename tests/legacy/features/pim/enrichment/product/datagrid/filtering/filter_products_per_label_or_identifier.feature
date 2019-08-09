@javascript
Feature: Filter products by label or identifier field
  In order to ease the search of products in the products grid
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully filter products on their label or identifier
    Given I add the "english" locale to the "mobile" channel
    And the following attributes:
      | label-en_US | type             | localizable | scopable | useable_as_grid_filter | group | code |
      | name        | pim_catalog_text | 1           | 1        | 1                      | other | name |
      | ean         | pim_catalog_text | 0           | 1        | 1                      | other | ean  |
      | isbn        | pim_catalog_text | 1           | 0        | 1                      | other | isbn |
    And the following families:
      | code   | attribute_as_label | attributes    |
      | office | name               | name,ean      |
      | home   | ean                | name,ean      |
      | book   | isbn               | name,ean,isbn |
    And the following products:
      | sku               | family | name-en_US-ecommerce | ean-ecommerce | isbn-en_US |
      | 125824            | office | Post it              | 2525          |            |
      | 6589              | office | paper                | post          |            |
      | mug               | home   | Mug                  | 2412          |            |
      | 50_shades_of_grey | book   | 50 shades of grey    | 1212          | 2424       |
    And I am on the products grid
    Then the grid should contain 4 elements
    And I should see products 125824, 6589, mug and 50_shades_of_grey
    And I should be able to use the following filters:
      | filter              | operator | value   | result                         |
      | label_or_identifier | equals   | 125824  | 125824                         |
      | label_or_identifier | equals   | post    | 125824                         |
      | label_or_identifier | equals   | post it | 125824                         |
      | label_or_identifier | equals   | paper   | 6589                           |
      | label_or_identifier | equals   | 2412    | mug                            |
      | label_or_identifier | equals   | pape    | 6589                           |
      | label_or_identifier | equals   | shade   | 50_shades_of_grey              |
      | label_or_identifier | equals   | 24      | 125824, mug, 50_shades_of_grey |
