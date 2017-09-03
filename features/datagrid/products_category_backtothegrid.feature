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
    And I am on the products grid

  @unstable
  Scenario: Successfully restore category filter without hashnav
    Given I filter by "category" with operator "" and value "summer_collection"
    And I am on the products grid
    Then I should see products purple-sneakers and black-sneakers
    And I should not see products black-boots

  @unstable
  Scenario: Successfully restore category filter with hashnav
    Given I filter by "category" with operator "" and value "winter_collection"
    And I click on the "black-sneakers" row
    And I should be on the product "black-sneakers" edit page
    And I am on the products grid
    Then I should see product black-sneakers
    And I should not see products purple-sneakers and black-boots

  @unstable
  Scenario: Successfully restore unclassified category filter without hashnav
    Given I filter by "category" with operator "unclassified" and value ""
    And I am on the products grid
    Then I should see products black-boots
    And I should not see products purple-sneakers and black-sneakers

  @unstable
  Scenario: Successfully restore unclassified category filter with hashnav
    Given I filter by "category" with operator "unclassified" and value ""
    And I click on the "black-boots" row
    And I should be on the product "black-boots" edit page
    And I am on the products grid
    Then I should see products black-boots
    And I should not see products purple-sneakers and black-sneakers

  @unstable
  Scenario: Successfully display the no results found message
    Given I filter by "sku" with operator "is equal to" and value "novalues"
    Then I should see the text "No results found. Try to change your search criteria."

  @jira https://akeneo.atlassian.net/browse/PIM-5638
  Scenario: Successfully apply category's filter on product grid without affecting other grids
    Given I open the category tree
    And I filter by "category" with operator "" and value "winter_collection"
    And I am on the imports page
    When I refresh the grid
    Then I should not see "Server error"
