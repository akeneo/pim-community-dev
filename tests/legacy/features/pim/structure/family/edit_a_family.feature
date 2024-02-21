@javascript
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
    Then I should see the text "Family successfully updated"
    And I should see the text "My family"

  Scenario: Fail switching attribute requirements when the user can't edit a family attributes
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Edit attributes of a family
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sneakers" family page
    And I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    When I switch the attribute "name" requirement in channel "tablet"
    Then attribute "name" should be required in channels mobile and tablet
