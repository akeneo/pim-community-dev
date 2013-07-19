@javascript
Feature: Filter products per category
  In order to enrich my catalog
  As an user
  I need to be able to manually filter products per category

  Background:
    Given the following product attributes:
      | label       | required |
      | SKU         | yes      |
    And the following products:
      | sku           |
      | purple-tshirt |
      | green-tshirt  |
      | akeneo-mug    |
    And the following categories:
      | code           | title   | products                   |
      | tshirts        | TShirts | purple-tshirt,green-tshirt |
    And I am logged in as "admin"

  Scenario: Successfully display all products classified in T-shirts on products page
    Given I am on the products page
    And I filter per category "tshirts"
    Then I should see products purple-tshirt and green-tshirt
    And I should not see products akeneo-mug
    
  Scenario: Successfully display all products unclassified on products page
    Given I am on the products page
    And I filter per unclassified category
    Then I should see products akeneo-mug
    And I should not see products purple-tshirt and green-tshirt