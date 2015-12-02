@javascript @jira https://akeneo.atlassian.net/browse/PIM-1920
Feature: Update product history when mass editing products
  In order see what changes have been made to products in mass edit
  As a product manager
  I need to be able to see the changes made in mass edit in product history

  Background:
    Given a "footwear" catalog configuration
    And the following products:
     | sku      | family   |
     | boots    | boots    |
     | sneakers | sneakers |
     | sandals  | sandals  |
    And I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products boots, sandals and sneakers

  Scenario: Display history when adding products to groups
    Given I choose the "Add to groups" operation
    And I check "Similar boots"
    And I move on to the next step
    And I wait for the "add-to-groups" mass-edit job to finish
    When I edit the "boots" product
    And I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
    When I edit the "sneakers" product
    And I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
    When I edit the "sandals" product
    And I open the history
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
