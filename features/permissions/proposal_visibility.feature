@javascript
Feature: Proposal tab should be visible
  In order view the proposals of a product
  As an administrator or a user
  I need to see a proposal tab

  Scenario: Proposal tab is visible even if I am not able to view associations
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
    And I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I remove rights to View the association types of a product
    And I save the role
    And I edit the "black-boots" product
    Then I should see the "Proposals" tab
