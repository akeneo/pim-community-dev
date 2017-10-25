@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3301
  Scenario: Successfully revert a product date and leave it empty
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | akeneo-jacket |
      | family | Jackets       |
    And I press the "Save" button in the popin
    And I wait to be on the "akeneo-jacket" product page
    And I switch the scope to "mobile"
    And I change the "Release date" to "05/20/2014"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "akeneo-jacket" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "akeneo-jacket" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully revert a date attribute with original empty value
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | akeneo-jacket |
      | family | Jackets       |
    And I press the "Save" button in the popin
    And I wait to be on the "akeneo-jacket" product page
    And I switch the scope to "mobile"
    When I change the "Release date" to "01/01/2001"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "akeneo-jacket" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "akeneo-jacket" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully revert a date attribute with original non empty value
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | akeneo-jacket |
      | family | Jackets       |
    And I press the "Save" button in the popin
    And I wait to be on the "akeneo-jacket" product page
    And I switch the scope to "mobile"
    And I change the "Release date" to "08/17/2011"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And I change the "Release date" to "01/01/2001"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "akeneo-jacket" has been built
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then the product "akeneo-jacket" should have the following values:
      | release_date-mobile | 2011-08-17 |
