@javascript @jira https://akeneo.atlassian.net/browse/PIM-1920
Feature: Update product history when mass editing products
  In order see what changes have been made to products in mass edit
  As a product manager
  I need to be able to see the changes made in mass edit in product history

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I save the product
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | sneakers |
      | Family | Sneakers |
    And I press the "Save" button in the popin
    And I wait to be on the "sneakers" product page
    And I save the product
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | sandals |
      | Family | Sandals |
    And I press the "Save" button in the popin
    And I wait to be on the "sandals" product page
    And I save the product
    And I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button

  Scenario: Display history when editing product attributes
    Given I choose the "Edit attributes values" operation
    And I display the Name attribute
    And I change the "Name" to "cool boots"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    When I edit the "boots" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value      | date |
      | 2       | Name en  | cool boots | now  |
    When I edit the "sandals" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value      | date |
      | 2       | Name en  | cool boots | now  |
    When I edit the "sneakers" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value      | date |
      | 2       | Name en  | cool boots | now  |
