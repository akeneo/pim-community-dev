@javascript
Feature: purge versions
  In order to clean the history list of versions for an entity and to lighten the database volume
  As an administrator
  I need to be able to purge the versions of entities

  Scenario: Successfully purges versions but keeps the first and last version of a family
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "heels" family page
    When I visit the "Properties" tab
    And I change the "English (United States)" to "My heels"
    And I press the "Save" button
    And I visit the "Attributes" tab
    And I add available attributes Weather conditions
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I switch the attribute "manufacturer" requirement in channel "mobile"
    And I press the "Save" button
    And I visit the "History" tab
    Then there should be 4 updates
    When I launch the purge versions command for entity "Akeneo\Pim\Structure\Component\Model\Family"
    And I am on the "heels" family page
    And I visit the "History" tab
    And I refresh current page
    Then there should be 2 updates
