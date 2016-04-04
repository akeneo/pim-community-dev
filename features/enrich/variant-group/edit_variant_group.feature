Feature: Edit a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to edit a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Properties" tab

  @javascript
  Scenario: Successfully edit a variant group
    Then I should see the Code and Axis fields
    And the fields Code and Axis should be disabled
    When I fill in the following information:
      | English (United States) | My boots |
    And I press the "Save" button
    Then I should see "My boots"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I fill in the following information:
      | English (United States) | My boots |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                          |
      | content | You will lose changes to the variant group if you leave this page. |

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I fill in the following information:
      | English (United States) | My boots |
    Then I should see "There are unsaved changes."

  @javascript
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
    And I press the "Save" button
    And I should see the text "89%"
    And I check the row "sneakers"
    And I press the "Save" button
    Then I should see the text "78%"
