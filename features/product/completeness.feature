@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As an user
  I need to be able to display the completeness of a product

  Background:
    Given the following families:
      | code      |
      | furniture |
      | phone     |
    And the following products:
      | sku         | family    | enabled |
      | postit      | furniture | yes     |
      | smartphone  | phone     | yes     |
    And a "postit" product
    And a "smartphone" product
    And the following product attributes:
      | label       | required | translatable | scopable |
      | SKU         | yes      | no           | no       |
      | name        | no       | yes          | no       |
      | image       | no       | no           | yes      |
      | description | no       | yes          | yes      |
    And the following product values:
      | product    | attribute   | locale |scope      | value                    |
      | postit     | SKU         |        |           | postit                   |
      | postit     | name        | en_US  |           | Post it                  |
      | postit     | name        | fr_FR  |           |                          |
      | postit     | image       |        | ecommerce | large.jpeg               |
      | postit     | image       |        | mobile    | small.jpeg               |
      | postit     | description | en_US  | ecommerce |                          |
      | postit     | description | en_US  | mobile    | My mobile description    |
      | postit     | description | fr_FR  | ecommerce | Ma description ecommerce |
      | postit     | description | fr_FR  | mobile    |                          |
      | smartphone | name        | fr_FR  |           | smartphone               |
    And the following attribute requirements:
      | family     | attribute   | scope     | required |
      | furniture  | name        | ecommerce | yes      |
      | furniture  | image       | ecommerce | yes      |
      | furniture  | description | ecommerce | yes      |
      | furniture  | name        | mobile    | yes      |
      | furniture  | description | mobile    | yes      |
      | phone      | name        | ecommerce | yes      |
      | phone      | name        | mobile    | no       |
    And I am logged in as "admin"

  Scenario: Successfully display the completeness of the product
    Given I am on the "postit" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see "warning" completeness with "1 missing values" message and 67% of ratio for channel "ecommerce" and locale "en"
    And I should see "warning" completeness with "1 missing values" message and 67% of ratio for channel "ecommerce" and locale "fr"
    And I should see "disabled" completeness with "Completed" message and 100% of ratio for channel "mobile" and locale "en"
    And I should see "danger" completeness with "2 missing values" message and 0% of ratio for channel "mobile" and locale "fr"

  Scenario: Successfully display the completeness for a second product
    Given I am on the "smartphone" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see "success" completeness with "Completed" message and 100% of ratio for channel "ecommerce" and locale "fr"
    And I should see "danger" completeness with "1 missing values" message and 0% of ratio for channel "ecommerce" and locale "en"
    And I should see "success" completeness with "Completed" message and 100% of ratio for channel "mobile" and locale "fr"
    And I should see "disabled" completeness with "Completed" message and 100% of ratio for channel "mobile" and locale "en"
