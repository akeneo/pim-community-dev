@javascript @restore-product-feature-enabled
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  # @jira https://akeneo.atlassian.net/browse/PIM-3760
  Scenario: Successfully revert a file attribute
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | t-shirt |
      | Family | Jackets |
    And I press the "Save" button in the popin
    And I wait to be on the "t-shirt" product page
    And I am on the "t-shirt" product page
    And I visit the "Media" group
    And I attach file "bic-core-148.txt" to "Datasheet"
    And I visit the "Product information" group
    And I change the Name to "T-shirt with datasheet"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I visit the "Media" group
    When I remove the "Datasheet" file
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "t-shirt" has been built
    When I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2 and then see 4 total versions
    And I visit the "Attributes" column tab
    And I visit the "Media" group
    Then the product "t-shirt" should have the following values:
      | datasheet | bic-core-148.txt |
