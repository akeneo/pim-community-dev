@javascript
Feature: Enforce rights to manage permissions for attribute groups, categories, locales and job profiles
  In order to be able to prevent some users from modifying permissions
  As an administrator
  I need to be able to enforce rights to manage permissions for attribute groups, categories, locales and job profiles

  @skip @info Unskip on PIM-6516
  Scenario: Display the Permissions tab only if user has the rights to manage permissions
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    When I am on the "media" attribute group page
    Then I should see the text "Permissions"
    When I am on the "2014_collection" category page
    Then I should see the text "Permissions"
    When I am on the "en_US" locale page
    Then I should see the text "Permissions"
    When I am on the "attribute_export" export job edit page
    Then I should see the text "Permissions"
    When I am on the "category_import" import job edit page
    Then I should see the text "Permissions"
    When I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Manage locale permissions, Manage category permissions, Manage attribute group permissions, Manage export profile permissions and Manage import profile permissions
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "en_US" locale page
    Then I should not see the text "Permissions"
    When I am on the "media" attribute group page
    Then I should not see the text "Permissions"
    When I am on the "2014_collection" category page
    Then I should not see the text "Permissions"
    When I am on the "attribute_export" export job edit page
    Then I should not see the text "Permissions"
    When I am on the "category_import" import job edit page
    Then I should not see the text "Permissions"
