@javascript
Feature: Remove a category
  In order to be able to remove an unused category
  As a user
  I need to be able to remove a category

  Background:
  Given the following product attributes:
    | label | required |
    | SKU   | yes      |
  And the following products:
    | sku       |
    | shoes-f-1 |
    | shoes-f-2 |
    | shoes-m-1 |
    | shoes-mf  |
  And the following category:
    | code      | label     | parent    | products             |
    | master    | Master    |           |                      |
    | books     | Books     | master    |                      |
    | computers | Computers | master    |                      |
    | notebooks | Notebooks | computers |                      |
    | ipad      | I-Pad     | computers |                      |
    | desktops  | Desktops  | computers |                      |
    | servers   | Servers   | computers |                      |
    | shoes     | Shoes     | master    | shoes-mf             |
    | shoes_f   | Female    | shoes     | shoes-f-1, shoes-f-2 |
    | shoes_m   | Male      | shoes     | shoes-m-1            |
  And I am logged in as "admin"

  Scenario: Remove a simple category
    Given I am on the "books" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "master" edit page
    And I should see flash message "Category successfully removed"
    And I should not see the "Books" category under the "Master" category
    And I should see the "Computers" category under the "Master" category

  Scenario: Remove a category with sub-categories
    Given I am on the "computers" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "master" edit page
    And I should see flash message "Category successfully removed"
    And I should not see the "Computers" category under the "Master" category

  Scenario: Remove a category with products linked
    Given I am on the "shoes_f" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "shoes" edit page
    And I should see flash message "Category successfully removed"
    When I expand the "Shoes" category
    Then I should not see the "Female" category under the "Shoes" category
    And I should see the "Male" category under the "Shoes" category

  Scenario: Remove a category with sub-categories and products linked
    Given I am on the "shoes" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "master" edit page
    And I should see flash message "Category successfully removed"
    And I should not see the "Shoes" category under the "Master" category

  Scenario: Remove a category tree
    Given I am on the "master" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be redirected on the category tree creation page
    And I should see flash message "Tree successfully removed"

  Scenario: Cancel the remove of a category
    Given I am on the "shoes" category page
    When I press the "Delete" button
    And I cancel the deletion
    Then I should see the "Shoes" category under the "Master" category
