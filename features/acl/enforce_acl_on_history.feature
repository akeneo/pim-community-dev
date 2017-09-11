@javascript
Feature: Enforce ACL on history
  In order to control who can view the history of different entities
  As an administrator
  I need to be able to define rights to see the history

  Scenario: Successfully hide association type history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "X_SELL" association type page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View association type history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "X_SELL" association type page
    Then I should not see "history"

  Scenario: Successfully hide attribute group history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "Sizes" attribute group page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View attribute group history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "Sizes" attribute group page
    Then I should not see "history"

  Scenario: Successfully hide attribute history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "color" attribute page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View attribute history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "color" attribute page
    Then I should not see "history"

  Scenario: Successfully hide category history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "sandals" category page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View category history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sandals" category page
    Then I should not see "history"

  Scenario: Successfully hide channel history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "mobile" channel page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View channel history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "mobile" channel page
    Then I should not see "history"

  Scenario: Successfully hide family history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "boots" family page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View family history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "boots" family page
    Then I should not see "history"

  Scenario: Successfully hide group history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "similar_boots" product group page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View group history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "similar_boots" product group page
    Then I should not see "history"

  @skip @info Will be removed in PIM-6444
  Scenario: Successfully hide variant group history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "caterpillar_boots" variant group page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View group variant history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "caterpillar_boots" variant group page
    Then I should not see "history"

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
    Then I should not see "history"

  Scenario: Successfully hide export profile history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "csv_footwear_option_export" export job edit page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View export profile history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "csv_footwear_option_export" export job page
    Then I should not see "history"

  Scenario: Successfully hide import profile history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "csv_footwear_group_import" import job edit page
    And I should see the text "history"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource View import profile history
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "csv_footwear_group_import" import job edit page
    Then I should not see "history"
