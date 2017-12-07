@javascript
Feature: Associate a product
  In order to create associations between products and groups
  As a product manager
  I need to associate a product to other products and groups

  Background:
    Given a "footwear" catalog configuration
    And the following product groups:
      | code              | label-en_US      | type    |
      | caterpillar_boots | Caterpillar boots| RELATED |
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
    And I press the "Add associations" button
    And I check the row "shoelaces"
    And I press the "Confirm" button in the popin
    Then I should see the text "1 product(s) and 0 group(s)"
    Then I should see product "shoelaces"

  @jira https://akeneo.atlassian.net/browse/PIM-4788
  Scenario: Associate a product to another group
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I visit the "Upsell" association type
    And I visit the "Display groups" target
    And I check the row "caterpillar_boots"
    And I save the product
    And I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    Then I should see the text "0 product(s) and 1 group(s)"
    And the row "caterpillar_boots" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-4788
  Scenario: Associate a product to multiple products and groups
    Given I edit the "black-boots" product
    And I visit the "Associations" column tab
    And I visit the "Substitution" association type
    And I press the "Add associations" button
    And I check the row "charcoal-boots"
    And I press the "Confirm" button in the popin
    And I visit the "Upsell" association type
    And I press the "Add associations" button
    And I check the row "charcoal-boots"
    And I press the "Confirm" button in the popin
    And I visit the "Display groups" target
    And I check the row "caterpillar_boots"
    And I save the product
    And I visit the "Cross sell" association type
    And I check the row "similar_boots"
    And I save the product
    And I visit the "Display products" target
    And I press the "Add associations" button
    And I check the rows "shoelaces, gray-boots, brown-boots and green-boots"
    And I press the "Confirm" button in the popin
    Then I should not see the text "There are unsaved changes."
    And I should see the text "4 product(s) and 1 group(s)"
    And I visit the "Upsell" association type
    Then I should see the text "1 product(s) and 1 group(s)"
    And I visit the "Substitution" association type
    Then I should see the text "1 product(s) and 0 group(s)"
    And I visit the "Pack" association type
    Then I should see the text "0 product(s) and 0 group(s)"

  @jira https://akeneo.atlassian.net/browse/PIM-4670
  Scenario: Keep association selection between tabs
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "gray-boots"
    And I check the row "black-boots"
    And I visit the "Pack" association type
    And I check the row "brown-boots"
    And I visit the "Substitution" association type
    And I visit the "Display groups" target
    And I check the row "similar_boots"
    And I visit the "Attributes" column tab
    And I visit the "Associations" column tab
    And I visit the "Cross sell" association type
    And I visit the "Display products" target
    Then the row "gray-boots" should be checked
    And the row "black-boots" should be checked
    When I visit the "Pack" association type
    Then the row "brown-boots" should be checked
    When I visit the "Substitution" association type
    And I visit the "Display groups" target
    Then the row "similar_boots" should be checked
    When I save the product
    And I visit the "Cross sell" association type
    And I visit the "Display products" target
    And I uncheck the rows "black-boots"
    And I visit the "Upsell" association type
    And I check the rows "black-boots"
    And I check the rows "shoelaces"
    And I visit the "Display groups" target
    And I check the rows "caterpillar_boots"
    And I visit the "Cross sell" association type
    Then the row "caterpillar_boots" should not be checked
    And I visit the "Display products" target
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

  @jira https://akeneo.atlassian.net/browse/PIM-5295
  Scenario: Association product grid is not filtered by the category selected in the product grid
    Given I am on the products grid
    When I open the category tree
    And I filter by "category" with operator "" and value "summer_collection"
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
    And I visit the "Display groups" target
    And I filter by "type" with operator "equals" and value "[RELATED]"
    When I edit the "gray-boots" product
    Then I should see the text "SUBSTITUTION"
    And I should see the text "Display groups"
    And the criteria of "type" filter should be "[RELATED]"
    When I visit the "Display products" target
    Then the criteria of "sku" filter should be "contains "gr""

  @jira https://akeneo.atlassian.net/browse/PIM-6110
  Scenario: Product associations are not erased when an attribute is saved
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "gray-boots"
    And I save the product
    And I visit the "Attributes" column tab
    Then I change the family of the product to "Boots"
    And I should see the text "Name"
    And I fill in "Name" with "test"
    And I save the product
    And I visit the "Associations" column tab
    Then the rows "gray-boots" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-6113
  Scenario: Do not keep saved product association groups after switching association type
    Given I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    And I visit the "Upsell" association type
    And I visit the "Display groups" target
    And I check the row "caterpillar_boots"
    And I save the product
    And I should not see the text "There are unsaved changes."
    When I visit the "Substitution" association type
    Then I should see the text "0 product(s) and 0 group(s)"
    And the row "caterpillar_boots" should not be checked

  @jira https://akeneo.atlassian.net/browse/PIM-6960
  Scenario: Don't make the pef fail after current association type removal
    Given I am on the association types page
    And I create a new association type
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | social_sell |
    And I press the "Save" button
    And I wait 5 seconds
    When I edit the "charcoal-boots" product
    When I visit the "Associations" column tab
    And I check the row "shoelaces"
    And I save the product
    Then I should see the text "1 product(s) and 0 group(s)"
    When I am on the association types page
    Then I should see association type social_sell
    When I click on the "Delete" action of the row which contains "social_sell"
    And I confirm the deletion
    Then I should not see association type social_sell
    When I edit the "charcoal-boots" product
    And I visit the "Associations" column tab
    Then I should see the text "black-boots"
