@javascript
Feature: Associate a product
  In order to create associations between products and groups
  As a product manager
  I need to associate a product to other products and groups

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku            |
      | charcoal-boots |
      | black-boots    |
      | gray-boots     |
      | brown-boots    |
      | green-boots    |
      | shoelaces      |
      | glossy-boots   |
    And I am logged in as "Julia"

  Scenario: Associate a product to another product
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "shoelaces"
    And I press the "Save" button
    Then I should see "1 products and 0 groups"
    Then the row "shoelaces" should be checked

  Scenario: Associate a product to another group
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" tab
    And I visit the "Upsell" group
    And I press the "Show groups" button
    And I check the row "Caterpillar boots"
    And I press the "Save" button
    Then I should see "0 products and 1 groups"
    And I press the "Show groups" button
    Then the row "Caterpillar boots" should be checked

  Scenario: Associate a product to multiple products and groups
    Given I edit the "black-boots" product
    When I visit the "Associations" tab
    And I visit the "Substitution" group
    And I check the row "charcoal-boots"
    And I visit the "Upsell" group
    And I check the row "glossy-boots"
    And I press the "Show groups" button
    And I check the row "Caterpillar boots"
    And I visit the "Cross sell" group
    And I check the row "Similar boots"
    And I press the "Show products" button
    And I check the rows "shoelaces, gray-boots, brown-boots and green-boots"
    And I press the "Save" button
    And I visit the "Cross sell" group
    Then I should see "4 products and 1 groups"
    And I visit the "Upsell" group
    Then I should see "1 products and 1 groups"
    And I visit the "Substitution" group
    Then I should see "1 products and 0 groups"
    And I visit the "Pack" group
    Then I should see "0 products and 0 groups"

  Scenario: Sort associated products
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "shoelaces"
    And I check the row "black-boots"
    And I press the "Save" button
    Then the row "shoelaces" should be checked
    And the row "black-boots" should be checked
    And I should be able to sort the rows by IS ASSOCIATED
    And I should be able to sort the rows by SKU

  @jira https://akeneo.atlassian.net/browse/PIM-4668
  Scenario: Detect unsaved changes when modifying associations
    Given I edit the "charcoal-boots" product
    When I visit the "Associations" tab
    And I select the "Cross sell" association
    And I check the row "gray-boots"
    And I check the row "black-boots"
    Then I should see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    Then I should see the text "There are unsaved changes."
    When I save the product
    Then I should not see the text "There are unsaved changes."
    When I visit the "Associations" tab
    And I select the "Cross sell" association
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
      | type   | product     |
      | X_SELL | black-boots |
      | X_SELL | gray-boots  |
    And I edit the "red-boots" product
    When I visit the "Associations" tab
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
      | type   | product     |
      | X_SELL | black-boots |
      | X_SELL | gray-boots  |
    And I edit the "red-boots" product
    When I visit the "Associations" tab
    Then I should be able to sort the rows by Is associated
