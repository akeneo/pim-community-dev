@javascript
Feature: Products back to the grid
  In order to restore the product grid filters
  As a regular user
  I need to be able to set filters and retrieve them after going back to the page

  Background:
    Given the "default" catalog configuration
    And a "sneakers_1" product
    And a "boots_1" product
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: Successfully restore filters without hashnav
    Given I filter by "sku" with operator "contains" and value "boots_1"
    And the grid should contain 1 element
    And I am on the products grid
    Then the grid should contain 1 element
    And the criteria of "sku" filter should be "contains "boots_1""
    And I should see product boots_1
    And I should not see product sneakers_1

  Scenario: Successfully restore filters with hashnav
    Given I filter by "sku" with operator "contains" and value "sneakers_1"
    And the grid should contain 1 element
    And I click on the "sneakers_1" row
    And I should be on the product "sneakers_1" edit page
    And I am on the products grid
    Then the grid should contain 1 element
    And the criteria of "sku" filter should be "contains "sneakers_1""
    And I should see product sneakers_1
    And I should not see product boots_1

  Scenario: Successfully restore page number with hashnav
    Given the following products:
      | sku        |
      | sneakers_0 |
      | sneakers_2 |
      | sneakers_3 |
      | sneakers_4 |
      | sneakers_5 |
      | sneakers_6 |
      | sneakers_7 |
      | sneakers_8 |
      | sneakers_9 |
    And I should be able to sort the rows by SKU
    And I change the page size to 10
    When I change the page number to 2
    And I click on the "boots_1" row
    And I should be on the product "boots_1" edit page
    And I am on the products grid
    Then the page number should be 2

  Scenario: Successfully restore the scope dropdown
    And I should see the text "Ecommerce"
    And I should not see the text "Mobile"
    And I click on the "sneakers_1" row
    And I should be on the product "sneakers_1" edit page
    When I switch the scope to "mobile"
    Then I should see the text "Mobile"
    And I should not see the text "Ecommerce"
    When I move backward one page
    Then I should see the text "Mobile"
    And I should not see the text "Ecommerce"
