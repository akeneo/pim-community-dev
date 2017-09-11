@javascript
Feature: Reset product grid filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to reset filters on the product grid

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku             | family  | categories        | size | color | groups        |
      | sandal-white-37 | sandals | summer_collection | 37   | white | similar_boots |
      | sandal-white-38 | sandals | summer_collection | 38   | white |               |
      | sandal-white-39 | sandals | summer_collection | 39   | white |               |
      | sandal-red-37   | sandals | summer_collection | 37   | red   | similar_boots |
      | sandal-red-38   | sandals | summer_collection | 38   | red   |               |
      | sandal-red-39   | sandals | summer_collection | 39   | red   |               |
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: I successfully reset attribute filters on the defaut view
    When I show the filter "color"
    And I hide the filter "family"
    And I filter by "color" with operator "in list" and value "White"
    Then the grid should contain 3 elements
    When I show the filter "size"
    And I filter by "size" with operator "in list" and value "37"
    Then the grid should contain 1 element
    When I reset the grid
    Then the grid should contain 6 elements
    And I should not see the text "Color: \"White\""
    And I should not see the text "Size: \"37\""

  Scenario: I successfully reset field filters on the defaut view
    When I filter by "family" with operator "in list" and value "Sandals"
    Then the grid should contain 6 elements
    When I filter by "groups" with operator "in list" and value "Similar boots"
    Then the grid should contain 2 elements
    When I reset the grid
    Then the grid should contain 6 elements
    And the criteria of "family" filter should be "All"
    And the criteria of "groups" filter should be "All"

  Scenario: I successfully keep the scope I work on when I reset the defaut view
    When I switch the scope to "Mobile"
    And I reset the grid
    Then I should see the text "Mobile"
    When I reload the page
    Then the grid should contain 6 elements
    And I should see the text "Mobile"

  Scenario: I successfully reset attribute filters of an existing view
    When I show the filter "color"
    And I hide the filter "family"
    And I filter by "color" with operator "in list" and value "White"
    Then the grid should contain 3 elements
    When I show the filter "size"
    And I filter by "size" with operator "in list" and value "37"
    And I create the view:
      | new-view-label | White 37 |
    Then I should see the text "White 37"
    And the grid should contain 1 element
    And I hide the filter "family"
    And I hide the filter "completeness"
    When I filter by "color" with operator "in list" and value "White, Red"
    And I filter by "size" with operator "in list" and value "38"
    Then the grid should contain 2 elements
    When I reset the grid
    Then the criteria of "color" filter should be ""White""
    And the criteria of "size" filter should be ""37""
    And the grid should contain 1 element

  Scenario: I successfully reset field filters of an existing view
    When I filter by "family" with operator "in list" and value "Sandals"
    And I filter by "groups" with operator "in list" and value "Similar boots"
    Then the grid should contain 2 elements
    When I create the view:
      | new-view-label | Similar Sandals |
    Then I should see the text "Similar Sandals"
    And the grid should contain 2 elements
    When I filter by "family" with operator "in list" and value "Boots"
    And I filter by "groups" with operator "in list" and value "Caterpillar boots"
    And the grid should contain 0 element
    When I reset the grid
    Then the grid should contain 2 elements
    And the criteria of "family" filter should be ""Sandals""
    And the criteria of "groups" filter should be ""Similar boots""

  Scenario: I successfully keep the scope I work on when I reset an existing view
    When I open the category tree
    And I switch the scope to "Mobile"
    And I create the view:
      | new-view-label | My products |
    Then I should see the text "My products"
    And the grid should contain 6 elements
    When I reset the grid
    Then I should see the text "Mobile"
    When I reload the page
    Then I should see the text "My products"
    And the grid should contain 6 elements
    And I open the category tree
    And I should see the text "Mobile"
