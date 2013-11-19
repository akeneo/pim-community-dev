Feature: Edit a family
  In order to provide accurate information about a family
  As a user
  I need to be able to edit its code and the translations of its name

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the families page

  Scenario: Successfully display the edit view for a family
    Given I edit the "Sneakers" family
    Then I should see the Code field
    And the field Code should be disabled

  Scenario: Successfully edit a family
    Given I edit the "Sneakers" family
    When I fill in the following information:
      | English (United States) | My family |
    And I save the family
    Then I should see "Family successfully updated"
    And I should see "My family"

  Scenario: Successfully set the translations of the name
    Given I am on the "Boots" family page
    And I change the english Label to "NewBoots"
    And I save the family
    Then I should see the families NewBoots, Sandals and Sneakers

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "Boots" family page
    When I change the english Label to "NewBoots"
    Then I should see "There are unsaved changes."
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                  |
      | content | You will lose changes to the family if you leave the page. |
