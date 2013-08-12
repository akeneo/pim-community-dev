@javascript
Feature: Browse products by locale and scope
  In order to enrich my catalog
  As a user
  I need to be able to browse products data by locale and scope

  Background:
    Given the following family:
      | code      |
      | furniture |
    And the following products:
      | sku    | family    |
      | postit | furniture |
    And a "postit" product
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
    And I am logged in as "admin"

  Scenario: Successfully display relevant products data on products page
    Given I am on the products page
    When I switch the locale to "en_US"
    And I filter per channel Ecommerce
    Then I should see product "postit" with data postit, Post it, large.jpeg and My ecommerce description
    When I filter per channel Mobile
    Then I should see product "postit" with data postit, Post it, small.jpeg and My mobile description
    When I switch the locale to "fr_FR"
    And I filter per channel Ecommerce
    Then I should see product "postit" with data postit, Etiquette, large.jpeg and Ma description ecommerce
    When I filter per channel Mobile
    Then I should see product "postit" with data postit, Etiquette, small.jpeg and Ma description mobile

