@javascript
Feature: Product category back to the grid
  In order to restore the product grid filters
  As a user
  I need to be able to set a category filter and retrieve it after going back to the page

  Background:
    Given the following product attributes:
      | label | required |
      | SKU   | yes      |
    And the following products:
      | sku           |
      | purple-tshirt |
      | green-tshirt  |
      | akeneo-mug    |
    And the following categories:
      | code     | parent  | label    | products                   |
      | catalog  |         | Catalog  |                            |
      | tshirts  | catalog | TShirts  | purple-tshirt,green-tshirt |
      | trousers | catalog | Trousers |                            |
    And I am logged in as "admin"
    And I am on the products page
    And I select the "Catalog" tree

  Scenario: Successfully restore category filter without hashnav
    Given I filter per category "tshirts"
    And I am on the products page
    Then I should see products purple-tshirt and green-tshirt
    And I should not see products akeneo-mug

  Scenario: Successfully restore category filter with hashnav
    Given I filter per category "tshirts"
    And I click on the "purple-tshirt" row
    And I click back to grid
    Then I should see products purple-tshirt and green-tshirt
    And I should not see products akeneo-mug

  Scenario: Successfully restore unclassified category filter without hashnav
    Given I filter per unclassified category
    And I am on the products page
    Then I should see products akeneo-mug
    And I should not see products purple-tshirt and green-tshirt

  Scenario: Successfully restore unclassified category filter with hashnav
    Given I filter per unclassified category
    And I click on the "akeneo-mug" row
    And I click back to grid
    Then I should see products akeneo-mug
    And I should not see products purple-tshirt and green-tshirt
