@ce @optional @javascript
Feature: Activate an OAuth2 client application in the PIM
  In order to activate an App
  As Julia
  I need to be able to go through the Authorization tunnel

  @marketplace-activate-feature-enabled
  Scenario: julia is authorized to activate an App
    Given a "default" catalog configuration
    And the role "ROLE_CATALOG_MANAGER" has the ACL "Manage Apps"
    And the user "Julia" has the profile "product_manager"
    And I am logged in as "Julia"
    And I am on the marketplace page
    And I should see "App prototype" app
    When I click on "App prototype" activate button
    And the url matches "https://yell-extension-t2omu7tdaq-uc.a.run.app/activate?pim_url=http%3A%2F%2Fhttpd"
    And I click on the button "Connect"
    And the url matches "http://httpd/#/connect/apps/authorize?client_id=6ff52991-1144-45cf-933a-5c45ae58e71a"
    And I see "View, edit and delete products and product models"
    And I click on the consent checkbox
    And I click on the button "Confirm"
    Then I have the connected app "App prototype"
    And my connected app has the following ACLs:
      | name                   | enabled |
      | pim_api_overall_access | true    |
      | pim_api_product_list   | true    |
      | pim_api_product_edit   | true    |
      | pim_api_product_remove | true    |
    And it can exchange the authorization code for a token

  @marketplace-activate-feature-enabled
  Scenario: Julia can activate and authenticate in an App
    Given a "default" catalog configuration
    And the role "ROLE_CATALOG_MANAGER" has the ACL "Manage Apps"
    And the user "Julia" has the profile "product_manager"
    And I am logged in as "Julia"
    And I am on the marketplace page
    And I should see "App prototype" app
    When I click on "App prototype" activate button
    And the url matches "https://yell-extension-t2omu7tdaq-uc.a.run.app/activate?pim_url=http%3A%2F%2Fhttpd"
    And I click on the button "Connect with openId + profile + email"
    And the url matches "http://httpd/#/connect/apps/authorize?client_id=6ff52991-1144-45cf-933a-5c45ae58e71a"
    And I see "View your email address"
    And I click on the button "Allow and next"
    And I see "View, edit and delete products and product models"
    And I click on the consent checkbox
    And I click on the button "Confirm"
    Then I have the connected app "App prototype"
    And my connected app has the following ACLs:
      | name                   | enabled |
      | pim_api_overall_access | true    |
      | pim_api_product_list   | true    |
      | pim_api_product_edit   | true    |
      | pim_api_product_remove | true    |
    And it can exchange the authorization code for an id token
