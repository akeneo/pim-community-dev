@javascript
Feature: Display the variant product history
  In order to know by who, when and what changes have been made to a variant product
  As a product manager
  I need to have access to a variant product history

  Scenario: Display product updates
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the "tshirt-unique-color-kurt-l" product page
    When I visit the "Product" group
    And I change the "Weight" to "750 Gram"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When the history of the product "tshirt-unique-color-kurt-l" has been built
    And I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property | value |
      | 2       | Weight   | 750   |
