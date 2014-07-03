Feature: Edit an attribute group
  In order to manage existing attribute groups in the catalog
  As a product manager
  I need to be able to edit an attribute group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit an attribute group
    Given I am on the "sizes" attribute group page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My sizes |
    And I press the "Save" button
    Then I should see "My sizes"

  @javascript
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "media" attribute group page
    When I fill in the following information:
      | English (United States) | My media |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                            |
      | content | You will lose changes to the attribute group if you leave this page. |

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "media" attribute group page
    When I fill in the following information:
      | English (United States) | My media |
    Then I should see "There are unsaved changes."
