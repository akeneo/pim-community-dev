@javascript
Feature: Filter products per category
  In order to enrich my catalog
  As a user
  I need to be able to manually filter products per category

  Background:
    Given the "default" catalog configuration
    And the following product attributes:
      | label | required |
      | SKU   | yes      |
    And the following products:
      | sku           |
      | purple-tshirt |
      | green-tshirt  |
      | akeneo-mug    |
      | blue-jeans    |
    And the following categories:
      | code     | parent   | label    | products                   |
      | catalog  |          | Catalog  |                            |
      | tshirts  | catalog  | TShirts  | purple-tshirt,green-tshirt |
      | trousers | catalog  | Trousers |                            |
      | jeans    | trousers | Jeans    | blue-jeans                 |
    And I am logged in as "admin"

  Scenario: Successfully display all products classified in T-shirts on products page
    Given I am on the products page
    When I select the "Catalog" tree
    And I filter per category "tshirts"
    Then I should see products purple-tshirt and green-tshirt
    And I should not see products akeneo-mug and blue-jeans

  Scenario: Successfully display all products directly classified in Trousers on products page
    Given I am on the products page
    When I select the "Catalog" tree
    And I filter per category "trousers"
    Then I should not see products purple-tshirt, green-tshirt, akeneo-mug and blue-jeans

  Scenario: Successfully display all products classified in Trousers or its children on products page
    Given I am on the products page
    When I check the "Include sub-categories" switch
    And I select the "Catalog" tree
    And I filter per category "trousers"
    Then I should see products blue-jeans
    And I should not see products purple-tshirt, green-tshirt and akeneo-mug

  Scenario: Successfully display all products unclassified on products page
    Given I am on the products page
    When I select the "Catalog" tree
    And I filter per unclassified category
    Then I should see products akeneo-mug
    And I should not see products purple-tshirt, green-tshirt and blue-jeans

  Scenario: Successfully display all products on products page by default
    Given I am on the products page
    When I select the "Catalog" tree
    Then I should see products akeneo-mug, purple-tshirt, green-tshirt and blue-jeans

