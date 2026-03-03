@javascript
Feature: Filter products by label or identifier field
  In order to ease the search of products in the products grid
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully filter products on their label identifier or uuid
    Given I add the "english" locale to the "mobile" channel
    And the following attributes:
      | label-en_US | type             | localizable | scopable | useable_as_grid_filter | group | code |
      | name        | pim_catalog_text | 1           | 1        | 1                      | other | name |
      | ean         | pim_catalog_text | 0           | 1        | 1                      | other | ean  |
      | isbn        | pim_catalog_text | 1           | 0        | 1                      | other | isbn |
    And the following families:
      | code   | attribute_as_label | attributes        |
      | office | name               | sku,name,ean      |
      | home   | ean                | sku,name,ean      |
      | book   | isbn               | sku,name,ean,isbn |
    And the following products:
      | sku               | uuid                                 | family | name-en_US-ecommerce | ean-ecommerce | isbn-en_US |
      | 125824            | daa86948-db97-48d0-819f-a09a2bc51c7c | office | Post it              | 2525          |            |
      | 6589              | f6af927b-e26d-48a8-a749-e0a2a6bc9ae8 | office | paper                | post          |            |
      | mug               | ebd75aa5-43bb-4908-9dff-5f8eefa4e3a9 | home   | Mug                  | 2412          |            |
      | 50_shades_of_grey | d28accaf-c749-478d-b4ac-fb2cfbbe8085 | book   | 50 shades of grey    | 1212          | 2424       |
    And I am on the products grid
    Then the grid should contain 4 elements
    And I should see products 125824, 6589, mug and 50_shades_of_grey
    And I should be able to use the following filters:
      | filter              | operator | value                                | result                         |
      | label_or_identifier | equals   | 125824                               | 125824                         |
      | label_or_identifier | equals   | post                                 | 125824                         |
      | label_or_identifier | equals   | post it                              | 125824                         |
      | label_or_identifier | equals   | paper                                | 6589                           |
      | label_or_identifier | equals   | 2412                                 | mug                            |
      | label_or_identifier | equals   | pape                                 | 6589                           |
      | label_or_identifier | equals   | shade                                | 50_shades_of_grey              |
      | label_or_identifier | equals   | 24                                   | 125824, mug, 50_shades_of_grey |
      | label_or_identifier | equals   | ebd75aa5-43bb-4908-9dff-5f8eefa4e3a9 | mug                            |
