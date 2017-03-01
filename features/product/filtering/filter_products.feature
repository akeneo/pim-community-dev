@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | label-en_US | localizable | scopable | useable_as_grid_filter | group | type             | code        | sort_order |
      | Name        | 1           | 0        | 1                      | other | pim_catalog_text | name        | 1          |
      | Image       | 0           | 1        | 1                      | other | pim_catalog_text | image       | 4          |
      | Info        | 1           | 1        | 1                      | other | pim_catalog_text | info        | 3          |
      | Description | 0           | 0        | 0                      | other | pim_catalog_text | description | 2          |
    And the following products:
      | sku    | family    | enabled | name-en_US  | name-fr_FR   | info-en_US-ecommerce    | info-fr_FR-ecommerce     | info-fr_FR-mobile     | image-ecommerce  | image-mobile     |
      | postit | furniture | yes     | Post it     | Etiquette    | My ecommerce info       | Ma info ecommerce        | Ma info mobile        | large.jpeg       | small.jpeg       |
      | book   | library   | no      | Book        | Livre        | My ecommerce book info  | Ma info livre ecommerce  | Ma info livre mobile  | book_large.jpeg  | book_small.jpeg  |
      | book2  |           | yes     | Book2       | Livre2       | My ecommerce book2 info | Ma info livre2 ecommerce | Ma info livre2 mobile | book2_large.jpeg | book2_small.jpeg |
      | 01234  |           | yes     | 01234       | 01234        | My ecommerce 01234 info | Ma info 01234 ecommerce  | Ma info 01234 mobile  |                  |                  |
      | ebook  |           | yes     | eBook       | Ebook        | My ecommerce ebook info | Ma info ebook ecommerce  | Ma info ebook mobile  |                  |                  |
      | chair  | furniture | yes     | Chair/Slash | Chaise/Slash | My ecommerce chair .    | Ma info chaise ecommerce | Ma info chaise mobile |                  |                  |
    And I am logged in as "Mary"

  Scenario: Successfully filter products
    Given I am on the products page
    Then the grid should contain 6 elements
    And I should see products postit, book, book2, ebook, chair and 01234
    And I should be able to use the following filters:
      | filter  | operator         | value         | result                                      |
      | sku     | contains         | book          | book, ebook and book2                       |
      | name    | contains         | post          | postit                                      |
      | info    | contains         | book          | book, ebook and book2                       |
      | enabled |                  | Enabled       | postit, ebook, book2, chair and 01234       |
      | enabled |                  | Disabled      | book                                        |
      | sku     | does not contain | book          | postit and chair and 01234                  |
      | sku     | starts with      | boo           | book and book2                              |
      | sku     | starts with      | 0             | 01234                                       |
      | sku     | is equal to      | book          | book                                        |
      | sku     | ends with        | book          | book and ebook                              |
      | sku     | in list          | book          | book                                        |
      | sku     | in list          | postit, book2 | postit and book2                            |
      | name    | is empty         |               |                                             |
      | name    | is not empty     |               | postit, book, ebook, book2, chair and 01234 |

  Scenario: Successfully hide/show filters
    Given I am on the products page
    Then I should see the filters sku, family and enabled
    Then I should not see the filters name, image and info
    When I show the filter "name"
    And I show the filter "info"
    And I hide the filter "sku"
    Then I should see the filters name, info, family and enabled
    And I should not see the filters Image, sku

  Scenario: Successfully order available filters
    Given I am on the products page
    And I should not see the filters name, image and info
    Then I should see available filters in the following order "sku,name,info,image"

  Scenario: Successfully reset the filters
    Given I am on the products page
    Then I filter by "enabled" with operator "" and value "Enabled"
    And the grid should contain 5 elements
    When I reset the grid
    Then the grid should contain 6 elements

  Scenario: Successfully refresh the grid
    Given I am on the products page
    Then I filter by "enabled" with operator "" and value "Enabled"
    And the grid should contain 5 elements
    When I refresh the grid
    Then the grid should contain 5 elements

  @jira https://akeneo.atlassian.net/browse/PIM-5208
  Scenario: View only attribute filters that are usable as grid filters
    Given I am on the products page
    Then I should see the available filters sku, family, enabled
    And I should see the available filters name, image, info
    And I should not see the available filters description
