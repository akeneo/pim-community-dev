@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a user
  I need to be able to filter products in the catalog

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
      | product | attribute   | locale |scope      | value                    |
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

  Scenario: Successfully display filters
    Given I am on the products page
    Then I should see the filters SKU, Family and Enabled
    And I should not see the filters Name, Image, Description
    And the grid should contain 2 elements
    And I should see products postit and book

  Scenario: Successfully hide/show filters
    Given I am on the products page
    When I make visible the filter "Name"
    And I make visible the filter "Description"
    And I hide the filter "SKU"
    Then I should see the filters Name, Description, Family and Enabled
    And I should not see the filters Image, SKU

  @insulated
  Scenario: Successfully filter by SKU
    Given I am on the products page
    When I filter by "SKU" with value "book"
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit

  @insulated
  Scenario: Successfully filter by Name
    Given I am on the products page
    When I make visible the filter "Name"
    And I filter by "Name" with value "post"
    Then the grid should contain 1 element
    And I should see products postit
    And I should not see products book

  @insulated
  Scenario: Successfully filter by Description
    Given I am on the products page
    When I make visible the filter "Description"
    And I filter by "Description" with value "book"
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit

  @insulated
  Scenario: Successfully display enabled products
    Given I am on the products page
    When I filter by "Enabled" with value "yes"
    Then the grid should contain 1 element
    And I should see products postit
    And I should not see products book

  @insulated
  Scenario: Successfully display disabled products
    Given I am on the products page
    When I filter by "Enabled" with value "no"
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit

  @insulated
  Scenario: Successfully reset the filters
    Given I am on the products page
    Then I filter by "Enabled" with value "yes"
    And the grid should contain 1 element
    When I reset the grid
    Then the grid should contain 2 elements

  @insulated
  Scenario: Successfully refresh the grid
    Given I am on the products page
    Then I filter by "Enabled" with value "yes"
    And the grid should contain 1 element
    When I refresh the grid
    Then the grid should contain 1 element
