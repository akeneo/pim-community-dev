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
      | label | localizable | scopable | useable as grid filter |
      | Name  | yes         | no       | yes                    |
      | Image | no          | yes      | yes                    |
      | Info  | yes         | yes      | yes                    |
    And the following products:
      | sku    | family    | enabled | name-en_US  | name-fr_FR   | info-en_US-ecommerce    | info-en_US-mobile    | info-fr_FR-ecommerce     | info-fr_FR-mobile     | image-ecommerce  | image-mobile     |
      | postit | furniture | yes     | Post it     | Etiquette    | My ecommerce info       | My mobile info       | Ma info ecommerce        | Ma info mobile        | large.jpeg       | small.jpeg       |
      | book   | library   | no      | Book        | Livre        | My ecommerce book info  | My mobile book info  | Ma info livre ecommerce  | Ma info livre mobile  | book_large.jpeg  | book_small.jpeg  |
      | book2  |           | yes     | Book2       | Livre2       | My ecommerce book2 info | My mobile book2 info | Ma info livre2 ecommerce | Ma info livre2 mobile | book2_large.jpeg | book2_small.jpeg |
      | ebook  |           | yes     | eBook       | Ebook        | My ecommerce ebook info | My mobile ebook info | Ma info ebook ecommerce  | Ma info ebook mobile  |                  |                  |
      | chair  | furniture | yes     | Chair/Slash | Chaise/Slash | My ecommerce chair .    | My mobile chaise     | Ma info chaise ecommerce | Ma info chaise mobile |                  |                  |
    And I am logged in as "Mary"

  Scenario: Successfully filter products
    Given I am on the products page
    Then the grid should contain 5 elements
    And I should see products postit and book and book2 and ebook and chair
    And I should be able to use the following filters:
      | filter | value                 | result                         |
      | SKU    | book                  | book, ebook and book2          |
      | Name   | post                  | postit                         |
      | Info   | book                  | book, ebook and book2          |
      | Status | Enabled               | postit, ebook, book2 and chair |
      | Status | Disabled              | book                           |
      | SKU    | contains book         | book, book2 and ebook          |
      | SKU    | does not contain book | postit and chair               |
      | SKU    | starts with boo       | book and book2                 |
      | SKU    | is equal to book      | book                           |
      | SKU    | ends with book        | book and ebook                 |
      | SKU    | in list book          | book                           |
      | SKU    | in list postit, book2 | postit and book2               |
      | Name   | empty                 |                                |

#      | Name    | contains chair/       | chair                          |
#      | Name    | contains /            | chair                          |
#      | Name    | does not contains /   | book, ebook, book2 and postit  |
#      | Info    | does not contains .   | book, ebook, book2 and postit  |
#      | Info    | contains .            | chair                          |

  Scenario: Successfully hide/show filters
    Given I am on the products page
    Then I should see the filters SKU, Family and Status
    Then I should not see the filters Name, Image and Info
    When I show the filter "Name"
    And I show the filter "Info"
    And I hide the filter "SKU"
    Then I should see the filters Name, Info, Family and Status
    And I should not see the filters Image, SKU

  Scenario: Successfully reset the filters
    Given I am on the products page
    Then I filter by "Status" with value "Enabled"
    And the grid should contain 4 elements
    When I reset the grid
    Then the grid should contain 5 elements

  Scenario: Successfully refresh the grid
    Given I am on the products page
    Then I filter by "Status" with value "Enabled"
    And the grid should contain 4 elements
    When I refresh the grid
    Then the grid should contain 4 elements
