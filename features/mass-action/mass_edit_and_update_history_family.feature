@javascript @jira https://akeneo.atlassian.net/browse/PIM-1920
Feature: Update product history when mass editing products
  In order see what changes have been made to products in mass edit
  As a product manager
  I need to be able to see the changes made in mass edit in product history

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku      | family   |
      | boots    | boots    |
      | sneakers | sneakers |
      | sandals  | sandals  |

  Scenario: Display history when changing product family
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press "Bulk actions" on the "Bulk Actions" dropdown button
    And I choose the "Change family" operation
    And I change the Family to "Sandals"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    When I edit the "boots" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | family   | sandals |
    When I edit the "sneakers" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value   |
      | 2       | family   | sandals |
    When I edit the "sandals" product
    And I visit the "History" column tab
    Then there should be 1 update
