Feature: Edit a family
  In order to provide accurate information about a family
  As an administrator
  I need to be able to edit its code and the translations of its name

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a family
    Given I am on the "Sneakers" family page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My family |
    And I save the family
    Then I should see "Family successfully updated"
    And I should see "My family"

  Scenario: Successfully set the translations of the name
    Given I am on the "Boots" family page
    When I fill in the following information:
      | English (United States) | NewBoots |
    And I save the family
    Then I should see "NewBoots"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "Boots" family page
    And I fill in the following information:
      | English (United States) | NewBoots |
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                  |
      | content | You will lose changes to the family if you leave the page. |

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "Boots" family page
    And I fill in the following information:
      | English (United States) | NewBoots |
    Then I should see "There are unsaved changes."

  @javascript
  Scenario: Disable property fields when the user can't edit a family
    Given I am on the "Administrator" role page
    And I remove rights to Edit properties of a family
    And I save the role
    When I am on the "sneakers" family page
    Then the field Code should be disabled
    And the field Attribute used as label should be disabled
    And the field English (United States) should be disabled
    And I reset the "Administrator" rights

  @javascript
  Scenario: Disable attribute fields when the user can't edit a family
    Given I am on the "Administrator" role page
    And I remove rights to Edit attributes of a family
    And I save the role
    When I am on the "sneakers" family page
    And I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    When I switch the attribute "Name" requirement in channel "Tablet"
    Then attribute "name" should be required in channels mobile and tablet
    And I reset the "Administrator" rights
