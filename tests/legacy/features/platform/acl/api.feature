@javascript
Feature: Define user rights on the web API
  In order to assign or remove some rights to a group of users
  As an administrator
  I need to be able to assign/remove rights

  @info here we check only if ACLs on "Web API permissions" are saved. Check on access resources are done in integration tests
    Scenario: Successfully edit and apply user rights on web API
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    When I am on the "Administrator" role page
    And I visit the "Web API permissions" tab
    Then I revoke rights to API resource Web API permissions
    And I save the role
    When I am on the "Administrator" role page
    And I visit the "Web API permissions" tab
    Then I should see API resource Web API permissions revoked
