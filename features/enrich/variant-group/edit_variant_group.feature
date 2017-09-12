@javascript @skip
Feature: Edit a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to edit a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Properties" tab

  Scenario: Successfully edit a variant group
    Then the variant group property "Code" should be disabled
    And the variant group property "Axis" should be disabled
    When I fill in the variant group property "English (United States)" with "My boots"
    And I save the variant group
    And I reload the page
    Then I should see the text "My boots"

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    When I fill in the variant group property "English (United States)" with "My boots"
    And I click on the Akeneo logo
    Then I should see "You will lose changes to the variant group if you leave this page." in popup

  Scenario: Successfully display a message when there are unsaved changes
    When I fill in the variant group property "English (United States)" with "My boots"
    Then I should see the text "There are unsaved changes."

  @skip @info Will be removed in PIM-6444
  Scenario: Successfully edit a variant group and the completeness should be computed
    Given I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following products:
      | sku      | family   | manufacturer | weather_conditions | color | name-en_US | name-fr_FR  | price          | rating | size | lace_color  |
      | sneakers | sneakers | Converse     | hot                | blue  | Sneakers   | Espadrilles | 69 EUR, 99 USD | 4      | 43   | laces_white |
    And the following product values:
      | product  | attribute   | value                 | locale | scope  |
      | sneakers | description | Great sneakers        | en_US  | mobile |
      | sneakers | description | Really great sneakers | en_US  | tablet |
      | sneakers | description | Grandes espadrilles   | fr_FR  | mobile |
    And I visit the "Attributes" tab
    When I add available attributes Description
    And I visit the "Products" tab
    And I save the variant group
    And I should see the text "88%"
    And I check the row "sneakers"
    And I save the variant group
    Then I should see the text "77%"
