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
