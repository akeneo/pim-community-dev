@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As a user
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
    And I launched the completeness calculator

  Scenario: Successfully display the completeness of the product
    Given I am on the "postit" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary

    And I should see the completeness state "warning" for channel "ecommerce" and locale "English (United States)"
    And I should see the completeness message "1 missing values" for channel "ecommerce" and locale "English (United States)"
    And I should see the completeness ratio 67% for channel "ecommerce" and locale "English (United States)"

    And I should see the completeness state "warning" for channel "ecommerce" and locale "French (France)"
    And I should see the completeness message "1 missing values" for channel "ecommerce" and locale "French (France)"
    And I should see the completeness ratio 67% for channel "ecommerce" and locale "French (France)"

    And I should see the completeness state "disabled" for channel "mobile" and locale "English (United States)"
    And I should see the completeness message "Complete" for channel "mobile" and locale "English (United States)"
    And I should see the completeness ratio 100% for channel "mobile" and locale "English (United States)"

    And I should see the completeness state "danger" for channel "mobile" and locale "French (France)"
    And I should see the completeness message "2 missing values" for channel "mobile" and locale "French (France)"
    And I should see the completeness ratio 0% for channel "mobile" and locale "French (France)"

  Scenario: Successfully display the completeness for a second product
    Given I am on the "smartphone" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary

    And I should see the completeness state "success" for channel "ecommerce" and locale "French (France)"
    And I should see the completeness message "Complete" for channel "ecommerce" and locale "French (France)"
    And I should see the completeness ratio 100% for channel "ecommerce" and locale "French (France)"

    And I should see the completeness state "danger" for channel "ecommerce" and locale "English (United States)"
    And I should see the completeness message "1 missing values" for channel "ecommerce" and locale "English (United States)"
    And I should see the completeness ratio 0% for channel "ecommerce" and locale "English (United States)"

    And I should see the completeness state "success" for channel "mobile" and locale "French (France)"
    And I should see the completeness message "Complete" for channel "mobile" and locale "French (France)"
    And I should see the completeness ratio 100% for channel "mobile" and locale "French (France)"

    And I should see the completeness state "disabled" for channel "mobile" and locale "English (United States)"
    And I should see the completeness message "Complete" for channel "mobile" and locale "English (United States)"
    And I should see the completeness ratio 100% for channel "mobile" and locale "English (United States)"
