@javascript
Feature: Products back to the grid
  In order to restore the product grid filters
  As a user
  I need to be able to set filters and retrieve them after going back to the page

  Background:
    Given the following family:
      | code      |
      | furniture |
      | library   |
    And the following products:
      | sku    | family    | enabled |
      | postit | furniture | yes     |
      | book   | library   | no      |
    And a "postit" product
    And a "book" product
    And the following product attributes:
      | label       | required | translatable | scopable |
      | SKU         | yes      | no           | no       |
      | name        | no       | yes          | no       |
      | image       | no       | no           | yes      |
      | description | no       | yes          | yes      |
    And the following product values:
      | product | attribute   | locale | scope     | value                    |
      | postit  | SKU         |        |           | postit                   |
      | postit  | name        | en_US  |           | Post it                  |
      | postit  | name        | fr_FR  |           | Etiquette                |
      | postit  | image       |        | ecommerce | large.jpeg               |
      | postit  | image       |        | mobile    | small.jpeg               |
      | postit  | description | en_US  | ecommerce | My ecommerce description |
      | postit  | description | en_US  | mobile    | My mobile description    |
      | postit  | description | fr_FR  | ecommerce | Ma description ecommerce |
      | postit  | description | fr_FR  | mobile    | Ma description mobile    |
      | book    | SKU         |        |           | book                     |
      | book    | name        | en_US  |           | Book                     |
      | book    | name        | fr_FR  |           | Livre                    |
      | book    | image       |        | ecommerce | book_large.jpeg          |
      | book    | image       |        | mobile    | book_small.jpeg          |
      | book    | description | en_US  | ecommerce | My ecommerce book descr  |
      | book    | description | en_US  | mobile    | My mobile book descr     |
      | book    | description | fr_FR  | ecommerce | Ma descr livre ecommerce |
      | book    | description | fr_FR  | mobile    | Ma descr livre mobile    |
    And I am logged in as "admin"

  Scenario: Successfully restore filters without hashnav
    Given I am on the products page
    When I filter by "SKU" with value "book"
    And I am on the products page
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit

  Scenario: Successfully restore filters with hashnav
    Given I am on the products page
    When I filter by "SKU" with value "book"
    And I click on the "book" row
    And I click back to grid
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit
