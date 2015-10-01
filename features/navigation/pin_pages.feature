@javascript
Feature: Pin pages to pinbar
  In order to navigate easily between pages
  As a regular user
  I need to be able to pin pages

  Background:
    Given a "default" catalog configuration
    And the following products:
      | sku       |
      | pineapple |
      | potatoe   |
    And I am logged in as "Mary"

  Scenario: Add pages to the pinbar
    Given I am on the "pineapple" product page
    Then the product SKU should be "pineapple"
    When I pin the current page
    And I am on the "potatoe" product page
    And I click on the pinned item "Products pineapple | Edit"
    Then I should be on the product "pineapple" edit page
    When I am on the "potatoe" product page
    And I refresh current page
    And I click on the pinned item "Products pineapple | Edit"
    Then I should be on the product "pineapple" edit page
