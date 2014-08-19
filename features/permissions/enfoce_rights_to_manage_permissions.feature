@javascript
Feature: Enforce rights to manage permissions for attribute groups, categories, locales and job profiles
  In order to be able to prevent some users from modifying permissions
  As an administrator
  I need to be able to enforce rights to manage permissions for attribute groups, categories, locales and job profiles

  Scenario: Don't display the Permissions tab if user doesn't have the rights to manage permissions
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the "Catalog manager" role page
    And I remove rights to Manage locale permissions
    And I remove rights to Manage category permissions
    And I remove rights to Manage attribute group permissions
    And I remove rights to Manage export profile permissions
    And I remove rights to Manage import profile permissions
    And I save the role
    When I am on the "media" attribute group page
    Then I should not see "Permissions"
    When I am on the "2014_collection" category page
    Then I should not see "Permissions"
    When I am on the "en_US" locale page
    Then I should not see "Permissions"
    When I am on the "attribute_export" export job edit page
    Then I should not see "Permissions"
    When I am on the "category_import" import job edit page
    Then I should not see "Permissions"
    And I reset the "Administrator" rights
