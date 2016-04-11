@javascript
Feature: Edit a channel
  In order to manage existing channels in the catalog
  As an administrator
  I need to be able to edit a channel

  Background:
    Given a "footwear" catalog configuration

  Scenario: Successfully edit a channel
    Given I am logged in as "Peter"
    And I am on the "tablet" channel page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | Default label | My tablet |
    And I press the "Save" button
    Then I should see the text "My tablet"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am logged in as "Peter"
    And  I am on the "mobile" channel page
    When I fill in the following information:
      | Default label | My mobile |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                    |
      | content | You will lose changes to the channel if you leave this page. |

  Scenario: Successfully display a message when there are unsaved changes
    Given I am logged in as "Peter"
    And  I am on the "mobile" channel page
    When I fill in the following information:
      | Default label | My mobile |
    Then I should see the text "There are unsaved changes."

  Scenario: Successfully edit a channel to enable a locale and disable unused locales when deleting a channel
    Given I am logged in as "Peter"
    And  I am on the "tablet" channel page
    And I change the "Locales" to "Breton (France)"
    And I press the "Save" button
    When I am on the locales page
    And I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see locales "en_US" and "br_FR"
    When I am on the "tablet" channel page
    And I press the "Delete" button
    And I confirm the deletion
    And I am on the locales page
    Then the grid should contain 1 element
    And I should see locale "en_US"

  Scenario: Successfully display the translation of the unit of metrics
    Given I am logged in as "Julien"
    And  I am on the "tablet" channel page
    And I fill in the following information:
      | Length | Kilomètre |

  Scenario: Successfully retrieve the last visited tab
    Given I am on the "tablet" channel page
    And I visit the "History" tab
    And I am on the products page
    Then I am on the "tablet" channel page
    And I should see the text "version"
    And I should see the text "author"

  Scenario: Successfully retrieve the last visited tab after a save
    Given I am on the "tablet" channel page
    And I visit the "History" tab
    And I save the channel
    And I should see the text "version"
    And I should see the text "author"
