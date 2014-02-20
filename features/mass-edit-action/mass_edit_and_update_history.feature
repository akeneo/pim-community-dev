@javascript @jira https://akeneo.atlassian.net/browse/PIM-1920
Feature: Update product history when mass editing products
  In order see what changes have been made to products in mass edit
  As Julia
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

  Scenario: Display history when editing product attributes
    Given I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "cool boots"
    And I move on to the next step
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property   | value      |
      | 2       | name-en_US | cool boots |
    When I edit the "sandals" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property   | value      |
      | 2       | name-en_US | cool boots |
    When I edit the "sneakers" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property   | value      |
      | 2       | name-en_US | cool boots |

  Scenario: Display history when changing product status
    Given I choose the "Change status (enable / disable)" operation
    And I disable the products
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |
    When I edit the "sandals" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |
    When I edit the "sneakers" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |

  Scenario: Display history when changing product family
    Given I choose the "Change the family of products" operation
    And I change the Family to "Sandals"
    And I move on to the next step
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | family   | sandals |
    When I edit the "sneakers" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | family   | sandals |
    When I edit the "sandals" product
    And I visit the "History" tab
    Then there should be 1 update

  Scenario: Display history when adding products to groups
    Given I choose the "Add to groups" operation
    And I check "Similar boots"
    And I move on to the next step
    When I edit the "boots" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
    When I edit the "sneakers" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
    When I edit the "sandals" product
    And I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value         |
      | 2       | groups   | similar_boots |
