@javascript
Feature: Define user rights on the web API
  In order to assign or remove some rights to a group of users
  As an administrator
  I need to be able to assign/remove rights

  Background:
    Given the minimal catalog
    And the administrator "Peter Williams"
    And I am logged in as "Peter"

  @info here we check only if ACLs on "Web API permissions" are saved. Check on access resources are done in integration tests
  Scenario: Successfully edit and apply user rights on web API
    Given I am on the "Administrator" role page
    And I visit the "Web API permissions" tab
    And I revoke rights to API resource Web API permissions
    And I save the role
    When I am on the "Administrator" role page
    And I visit the "Web API permissions" tab
    Then I should see API resource Web API permissions revoked

  Scenario: Successfully edit and apply user rights on Manage the API connections
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Manage the API connections
    And I save the role
    When I am on the "Administrator" role page
    And I visit the "Permissions" tab
    Then I should see resources Manage the API connections revoked
