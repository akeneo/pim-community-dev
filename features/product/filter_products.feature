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
      | label       | required | translatable | scopable | useable as grid filter |
      | name        | no       | yes          | no       | yes                    |
      | image       | no       | no           | yes      | yes                    |
      | description | no       | yes          | yes      | yes                    |
    And the following products:
      | sku    | family    | enabled | name-en_US | name-fr_FR | description-en_US-ecommerce | description-en_US-mobile | description-fr_FR-ecommerce | description-fr_FR-mobile |
      | postit | furniture | yes     | Post it    | Etiquette  | My ecommerce description    | My mobile description    | Ma description ecommerce    | Ma description mobile    |
      | book   | library   | no      | Book       | Livre      | My ecommerce book descr     | My mobile book descr     | Ma descr livre ecommerce    | Ma descr livre mobile    |
      | book2  |           | yes     | Book2      | Livre2     | My ecommerce book2 descr    | My mobile book2 descr    | Ma descr livre2 ecommerce   | Ma descr livre2 mobile   |
      | ebook  |           | yes     | eBook      | Ebook      | My ecommerce ebook descr    | My mobile ebook descr    | Ma descr ebook ecommerce    | Ma descr ebook mobile    |
    And the following product values:
      | product | attribute | scope     | value            |
      | postit  | image     | ecommerce | large.jpeg       |
      | postit  | image     | mobile    | small.jpeg       |
      | book    | image     | ecommerce | book_large.jpeg  |
      | book    | image     | mobile    | book_small.jpeg  |
      | book2   | image     | ecommerce | book2_large.jpeg |
      | book2   | image     | mobile    | book2_small.jpeg |
    And I am logged in as "admin"

  Scenario: Successfully filter products
    Given I am on the products page
    Then the grid should contain 4 elements
    And I should see products postit and book and book2 and ebook
    And I should be able to use the following filters:
      | filter      | value                 | result                  |
      | SKU         | book                  | book, ebook and book2   |
      | Name        | post                  | postit                  |
      | Description | book                  | book, ebook and book2   |
      | Enabled     | yes                   | postit, ebook and book2 |
      | Enabled     | no                    | book                    |
      | SKU         | contains book         | book, book2 and ebook   |
      | SKU         | does not contain book | postit                  |
      | SKU         | starts with boo       | book and book2          |
      | SKU         | is equal to book      | book                    |
      | SKU         | ends with book        | book and ebook          |

  Scenario: Successfully hide/show filters
    Given I am on the products page
    Then I should see the filters SKU, Family and Enabled
    Then I should not see the filters Name, Image, Description
    When I show the filter "Name"
    And I show the filter "Description"
    And I hide the filter "SKU"
    Then I should see the filters Name, Description, Family and Enabled
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
