Feature: Edit a channel
  In order to manage existing channels in the catalog
  As a user
  I need to be able to edit a channel

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the edit view for a channel
    Given I am on the "tablet" channel page
    Then I should see the Code field
    And the field Code should be disabled

  Scenario: Successfully edit a channel
    Given I am on the "tablet" channel page
    When I fill in the following information:
      | Default label | My tablet |
    And I press the "Save" button
    Then I should see "My tablet"

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "mobile" channel page
    When I fill in the following information:
      | Default label | My mobile |
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                    |
      | content | You will lose changes to the channel if you leave this page. |

  Scenario: Successfully edit a channel to enable a locale
    Given a "sirup" product
    And the following attributes:
      | label       | required | translatable | scopable | useable as grid filter |
      | composition | no       | yes          | no       | yes                    |
    And the following product values:
      | product | attribute   | locale | scope     | value |
      | sirup   | SKU         |        |           | sirup |
      | sirup   | name        | en_US  |           | Sirup |
      | sirup   | name        | fr_FR  |           | Sirop |
    And I am on the "tablet" channel page
    And I select the locale "Breton (France)"
    And I press the "Save" button
    Then I should see "My tablet"


