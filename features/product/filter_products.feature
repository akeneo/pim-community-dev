@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | label  | translatable | scopable | useable as grid filter |
      | name   | yes          | no       | yes                    |
      | image  | no           | yes      | yes                    |
      | info   | yes          | yes      | yes                    |
    And the following products:
      | sku    | family    | enabled | name-en_US | name-fr_FR | info-en_US-ecommerce    | info-en_US-mobile    | info-fr_FR-ecommerce     | info-fr_FR-mobile     | image-ecommerce  | image-mobile     |
      | postit | furniture | yes     | Post it    | Etiquette  | My ecommerce info       | My mobile info       | Ma info ecommerce        | Ma info mobile        | large.jpeg       | small.jpeg       |
      | book   | library   | no      | Book       | Livre      | My ecommerce book info  | My mobile book info  | Ma info livre ecommerce  | Ma info livre mobile  | book_large.jpeg  | book_small.jpeg  |
      | book2  |           | yes     | Book2      | Livre2     | My ecommerce book2 info | My mobile book2 info | Ma info livre2 ecommerce | Ma info livre2 mobile | book2_large.jpeg | book2_small.jpeg |
      | ebook  |           | yes     | eBook      | Ebook      | My ecommerce ebook info | My mobile ebook info | Ma info ebook ecommerce  | Ma info ebook mobile  |                  |                  |
    And I am logged in as "admin"

  Scenario: Successfully filter products
    Given I am on the products page
    Then the grid should contain 4 elements
    And I should see products postit and book and book2 and ebook
    And I should be able to use the following filters:
      | filter  | value                 | result                  |
      | SKU     | book                  | book, ebook and book2   |
      | Name    | post                  | postit                  |
      | Info    | book                  | book, ebook and book2   |
      | Enabled | yes                   | postit, ebook and book2 |
      | Enabled | no                    | book                    |
      | SKU     | contains book         | book, book2 and ebook   |
      | SKU     | does not contain book | postit                  |
      | SKU     | starts with boo       | book and book2          |
      | SKU     | is equal to book      | book                    |
      | SKU     | ends with book        | book and ebook          |

  Scenario: Successfully hide/show filters
    Given I am on the products page
    Then I should see the filters SKU, Family and Enabled
    Then I should not see the filters Name, Image and Info
    When I show the filter "Name"
    And I show the filter "Info"
    And I hide the filter "SKU"
    Then I should see the filters Name, Info, Family and Enabled
    And I should not see the filters Image, SKU

  Scenario: Successfully reset the filters
    Given I am on the products page
    Then I filter by "Enabled" with value "yes"
    And the grid should contain 3 elements
    When I reset the grid
    Then the grid should contain 4 elements

  Scenario: Successfully refresh the grid
    Given I am on the products page
    Then I filter by "Enabled" with value "yes"
    And the grid should contain 3 elements
    When I refresh the grid
    Then the grid should contain 3 elements
