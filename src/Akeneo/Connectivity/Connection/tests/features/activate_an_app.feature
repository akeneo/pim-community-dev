@ce @optional-marketplace-activate @javascript
Feature: Activate an OAuth2 client application in the PIM
  In order to activate an App
  As Julia
  I need to be able to go through the Authorization tunnel

  Scenario: julia is authorized to activate an App
    Given I am logged in as "julia"
    And I have the “Manage Apps” ACL
    And I am on the marketplace page
    When I click on “yell” activate button
    And I am at the url “https://yell-extension-t2omu7tdaq-uc.a.run.app/?pim_url=...”
    And I click on the button “Free trial”
    And I am at the url “https://.../”
    And I see “View and Edit products”
    And I click on authorize button
    Then I have a new "yell" connection
    And it has an API token
    And ACL are well defined
