@javascript
Feature: Browse products by locale and scope
  In order to enrich my catalog
  As a user
  I need to be able to browse products data by locale and scope

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
    And the following products:
      | sku    | family    |
      | postit | furniture |
    And the following attributes:
      | label       | translatable | scopable | useable as grid column |
      | name        | yes          | no       | yes                    |
      | image       | no           | yes      | yes                    |
      | description | yes          | yes      | yes                    |
    And the following product values:
      | product | attribute   | locale | scope     | value                    |
      | postit  | name        | en_US  |           | Post it                  |
      | postit  | name        | fr_FR  |           | Etiquette                |
      | postit  | image       |        | ecommerce | large.jpeg               |
      | postit  | image       |        | mobile    | small.jpeg               |
      | postit  | description | en_US  | ecommerce | My ecommerce description |
      | postit  | description | fr_FR  | ecommerce | Ma description ecommerce |
      | postit  | description | fr_FR  | mobile    | Ma description mobile    |
    And I am logged in as "admin"
    And I am on the products page

  Scenario: Successfully display english data on products page
    Given I switch the locale to "English (United States)"
    Then I should see product postit
    When I filter by "Channel" with value "E-Commerce"
    Then the row "postit" should contain:
      | column      | value                    |
      | sku         | postit                   |
      | name        | Post it                  |
      | image       | large.jpeg               |
      | description | My ecommerce description |
      | family      | furniture                |
    When I filter by "Channel" with value "Mobile"
    Then the row "postit" should contain:
      | column      | value      |
      | SKU         | postit     |
      | name        | Post it    |
      | image       | small.jpeg |
      | description |            |
      | family      | furniture  |

  Scenario: Successfully display french data on products page
    Given I switch the locale to "French (France)"
    Then I should see product postit
    When I filter by "Channel" with value "E-Commerce"
    Then the row "postit" should contain:
      | column        | value                    |
      | SKU           | postit                   |
      | [name]        | Etiquette                |
      | [image]       | large.jpeg               |
      | [description] | Ma description ecommerce |
      | family        | furniture                |
    When I filter by "Channel" with value "Mobile"
    Then the row "postit" should contain:
      | column        | value                 |
      | SKU           | postit                |
      | [name]        | Etiquette             |
      | [image]       | small.jpeg            |
      | [description] | Ma description mobile |
      | family        | furniture             |
