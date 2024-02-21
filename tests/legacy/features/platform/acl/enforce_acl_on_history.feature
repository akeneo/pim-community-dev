@javascript
Feature: Enforce ACL on history
  In order to control who can view the history of different entities
  As an administrator
  I need to be able to define rights to see the history

  Scenario: Successfully hide product history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "boot" product page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View product history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    Then I should not see the text "history"
