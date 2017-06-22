@javascript
Feature: Associate a product
  In order to create associations between products and groups
  As a product manager
  I need to associate a product to other products and groups

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku            | categories        |
      | charcoal-boots | Summer_collection |
      | black-boots    |                   |
      | gray-boots     |                   |
      | brown-boots    |                   |
      | green-boots    |                   |
      | shoelaces      |                   |
      | glossy-boots   |                   |
    And I am logged in as "Julia"

  Scenario: Associate a product to another product
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "shoelaces"
    And I save the product
    Then I should see the text "1 products and 0 groups"
    Then the row "shoelaces" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-4788
  Scenario: Associate a product to another group
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I visit the "Upsell" association type
    And I press the "Show groups" button
    And I check the row "caterpillar_boots"
    And I save the product
    And I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    Then I should see the text "0 products and 1 groups"
    And the row "caterpillar_boots" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-4788
  Scenario: Associate a product to multiple products and groups
    Given I edit the "black-boots" product
    And I visit the "Associations" column tab
    And I visit the "Substitution" association type
    And I check the row "charcoal-boots"
    And I visit the "Upsell" association type
    And I check the row "glossy-boots"
    And I press the "Show groups" button
    And I check the row "caterpillar_boots"
    And I visit the "Cross sell" association type
    And I check the row "similar_boots"
    And I press the "Show products" button
    And I check the rows "shoelaces, gray-boots, brown-boots and green-boots"
    When I save the product
    Then I should not see the text "There are unsaved changes."
    And I should see the text "4 products and 1 groups"
    And I visit the "Upsell" association type
    Then I should see the text "1 products and 1 groups"
    And I visit the "Substitution" association type
    Then I should see the text "1 products and 0 groups"
    And I visit the "Pack" association type
    Then I should see the text "0 products and 0 groups"

  Scenario: Sort associated products
    Given I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    And I check the row "shoelaces"
    And I check the row "black-boots"
    When I save the product
    Then I should not see the text "There are unsaved changes."
    And the row "shoelaces" should be checked
    And the row "black-boots" should be checked
    And I should be able to sort the rows by Is associated
    And I should be able to sort the rows by SKU

  @jira https://akeneo.atlassian.net/browse/PIM-4670
  Scenario: Keep association selection between tabs
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "gray-boots"
    And I check the row "black-boots"
    And I visit the "Pack" association type
    And I check the row "glossy-boots"
    And I visit the "Substitution" association type
    And I press the "Show groups" button
    And I check the row "similar_boots"
    And I visit the "Attributes" column tab
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I press the "Show products" button
    Then the row "gray-boots" should be checked
    And the row "black-boots" should be checked
    When I visit the "Pack" association type
    Then the row "glossy-boots" should be checked
    When I visit the "Substitution" association type
    And I press the "Show groups" button
    Then the row "similar_boots" should be checked
    When I save the product
    And I visit the "Cross sell" association type
    And I press the "Show products" button
    And I uncheck the rows "black-boots"
    And I visit the "Upsell" association type
    And I check the rows "shoelaces"
    And I check the rows "black-boots"
    And I press the "Show groups" button
    And I check the rows "caterpillar_boots"
    And I visit the "Cross sell" association type
    Then the row "caterpillar_boots" should not be checked
    And I press the "Show products" button
    Then the row "black-boots" should not be checked

  @jira https://akeneo.atlassian.net/browse/PIM-4668
  Scenario: Detect unsaved changes when modifying associations
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "gray-boots"
    And I check the row "black-boots"
    Then I should see the text "There are unsaved changes."
    And I visit the "Attributes" column tab
    Then I should see the text "There are unsaved changes."
    When I save the product
    Then I should not see the text "There are unsaved changes."
    When I visit the "Associations" column tab
    And I uncheck the rows "black-boots"
    Then I should see the text "There are unsaved changes."
    And I check the rows "black-boots"
    # Wait for the fade-out of the message
    And I wait 1 seconds
    Then I should not see the text "There are unsaved changes."

  @jira https://akeneo.atlassian.net/browse/PIM-5161
  Scenario: Grid is sorted by default by "is associated"
    Given the following products:
      | sku          |
      | red-boots    |
      | purple-boots |
      | yellow-boots |
      | orange-boots |
      | white-boots  |
    And the following associations for the product "red-boots":
      | type   | products    |
      | X_SELL | black-boots |
      | X_SELL | gray-boots  |
    And I edit the "red-boots" product
    When I visit the "Associations" column tab
    Then I should see the text "black-boots"
    And I should see the text "gray-boots"
    And the rows "black-boots and gray-boots" should be checked
    And the rows should be sorted descending by Is associated

  @jira https://akeneo.atlassian.net/browse/PIM-5161
  Scenario: Grid is sortable by "is associated"
    Given the following products:
      | sku          |
      | red-boots    |
      | purple-boots |
      | yellow-boots |
      | orange-boots |
      | white-boots  |
    And the following associations for the product "red-boots":
      | type   | products    |
      | X_SELL | black-boots |
      | X_SELL | gray-boots  |
    And I edit the "red-boots" product
    When I visit the "Associations" column tab
    Then I should be able to sort the rows by Is associated

  @jira https://akeneo.atlassian.net/browse/PIM-5295
  Scenario: Association product grid is not filtered by the category selected in the product grid
    Given I am on the products page
    When I filter by "category" with operator "" and value "summer_collection"
    Then I should see product charcoal-boots
    And I should not see product black-boots
    When I am on the "charcoal-boots" product page
    And I visit the "Associations" column tab
    Then the grid should contain 6 elements
    When I visit the "Upsell" association type
    Then the grid should contain 6 elements

  @jira https://akeneo.atlassian.net/browse/PIM-5593
  Scenario: Keep product associations grids context
    Given I edit the "shoelaces" product
    And I visit the "Associations" column tab
    And I visit the "Substitution" association type
    Then the grid should contain 6 elements
    When I filter by "sku" with operator "Contains" and value "gr"
    And I press the "Show groups" button
    And I filter by "type" with operator "equals" and value "[RELATED]"
    When I edit the "gray-boots" product
    Then I should see the text "Association type: Substitution"
    And I should see the text "Show products"
    And the criteria of "type" filter should be "[RELATED]"
    When I press the "Show products" button
    Then the criteria of "sku" filter should be "contains "gr""

  @jira https://akeneo.atlassian.net/browse/PIM-6110
  Scenario: Product associations are not erased when an attribute is saved
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "gray-boots"
    And I save the product
    And I visit the "Attributes" column tab
    And I add available attributes Name
    And I fill in "Name" with "test"
    And I save the product
    And I visit the "Associations" column tab
    Then the rows "gray-boots" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-6113
  Scenario: Do not keep saved product association groups after switching association type
    Given I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    And I press the "Show groups" button
    And I check the row "caterpillar_boots"
    And I save the product
    And I should not see the text "There are unsaved changes."
    When I visit the "Substitution" association type
    Then I should see the text "0 products and 0 groups"
    And the row "caterpillar_boots" should not be checked
