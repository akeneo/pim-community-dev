@javascript
Feature: Product category back to the grid
  In order to restore the product grid filters
  As a regular user
  I need to be able to set a category filter and retrieve it after going back to the page

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku             | categories                           |
      | purple-sneakers | summer_collection                    |
      | black-sneakers  | summer_collection, winter_collection |
      | black-boots     |                                      |
    And I am logged in as "Mary"
    And I am on the products page

  @unstable
  Scenario: Successfully restore category filter without hashnav
    Given I filter by "category" with value "summer_collection"
    And I am on the products page
    Then I should see products purple-sneakers and black-sneakers
    And I should not see products black-boots

  @unstable
  Scenario: Successfully restore category filter with hashnav
    Given I filter by "category" with value "winter_collection"
    And I click on the "black-sneakers" row
    And I click back to grid
    Then I should see product black-sneakers
    And I should not see products purple-sneakers and black-boots

  @unstable
  Scenario: Successfully restore unclassified category filter without hashnav
    Given I filter by "category" with value "unclassified"
    And I am on the products page
    Then I should see products black-boots
    And I should not see products purple-sneakers and black-sneakers

  @unstable
  Scenario: Successfully restore unclassified category filter with hashnav
    Given I filter by "category" with value "unclassified"
    And I click on the "black-boots" row
    And I click back to grid
    Then I should see products black-boots
    And I should not see products purple-sneakers and black-sneakers

  @unstable
  Scenario: Successfully display the no results found message
    Given I filter by "SKU" with value "novalues"
    Then I should see "No results found. Try to change your search criteria."
