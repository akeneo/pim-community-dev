@javascript
Feature: Display the product history
  In order to know who, when and what changes has been made to a product
  As a product manager
  I need to have access to a product history

  Scenario: Display product updates and published version
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | sandals-001 |
      | Family | Sandals     |
    And I press the "Save" button in the popin
    And I wait to be on the "sandals-001" product page
    And I fill in the following information:
      | Name | BG sandals |
    And I save the product
    And I press the secondary action "Publish"
    And I confirm the publishing
    And I fill in the following information:
      | SKU | sandals-001-bis |
    And I save the product
    When I visit the "History" column tab
    Then there should be 3 update
    And I should see history:
      | version | property | value           |
      | 1       | SKU      | sandals-001     |
      | 2       | Name en  | BG sandals      |
      | 3       | SKU      | sandals-001-bis |
    And the version 2 should be marked as published
