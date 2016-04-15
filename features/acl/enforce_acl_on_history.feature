@javascript
Feature: Enforce ACL on history
  In order to control who can view the history of different entities
  As an administrator
  I need to be able to define rights to see the history

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "X_SELL" association type page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View association type history
    And I save the role
    When I am on the "X_SELL" association type page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "Sizes" attribute group page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View attribute group history
    And I save the role
    When I am on the "Sizes" attribute group page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "color" attribute page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View attribute history
    And I save the role
    When I am on the "color" attribute page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "sandals" category page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View category history
    And I save the role
    When I am on the "sandals" category page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "mobile" channel page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View channel history
    And I save the role
    When I am on the "mobile" channel page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "boots" family page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View family history
    And I save the role
    When I am on the "boots" family page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "similar_boots" product group page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View group history
    And I save the role
    When I am on the "similar_boots" product group page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "boot" product page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View product history
    And I save the role
    When I am on the "boot" product page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "csv_footwear_option_export" export job edit page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View export profile history
    And I save the role
    When I am on the "csv_footwear_option_export" export job page
    Then I should not see "history"

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    And I am on the "csv_footwear_group_import" import job edit page
    And I should see "history"
    And I am on the "Administrator" role page
    And I remove rights to View import profile history
    And I save the role
    When I am on the "csv_footwear_group_import" import job edit page
    Then I should not see "history"
