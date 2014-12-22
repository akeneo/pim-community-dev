@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert the status of a product (disabled)
    Given an enabled "shirt" product
    And I am on the "shirt" product page
    And I disable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: shirt"
    Then product "shirt" should be enabled

  Scenario: Successfully revert the status of a product (enable)
    Given a disabled "shirt" product
    And I am on the "shirt" product page
    And I enable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: shirt"
    Then product "shirt" should be disabled

  Scenario: Successfully revert the family of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    And I am on the products page
    Then I mass-edit products jean
    And I choose the "Change the family of products" operation
    And I change the Family to "Jackets"
    And I move on to the next step
    Then the family of product "jean" should be "jackets"
    And I am on the "jean" product page
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then the family of product "jean" should be "pants"

  Scenario: Successfully revert the category of a product
    Given the following product:
      | sku     | categories        |
      | sandals | winter_collection |
    And I edit the "sandals" product
    And I visit the "Categories" tab
    And I select the "2014 collection" tree
    And I expand the "2014 collection" category
    And I click on the "Winter collection" category
    And I click on the "Summer collection" category
    And I press the "Save" button
    Then I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: sandals"
    Then the category of "sandals" should be "winter_collection"

  Scenario: Successfully revert simpleselect attribute options of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    Given I am on the "jean" product page
    And I change the Manufacturer to "Desigual"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then I should see a flash message "Successfully revert the product to the previous version"

  Scenario: Successfully revert multiselect attribute options of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    Given I am on the "jean" product page
    Given I add a new option to the "Weather conditions" attribute
    When I fill in the following information in the popin:
      | Code  | very_wet      |
      | en_US | Extremely wet |
    And I press the "Save" button in the popin
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then I should see a flash message "Successfully revert the product to the previous version"

  @jira https://akeneo.atlassian.net/browse/PIM-3351
  Scenario: Successfully revert a product with prices and leave them empty
    And the following product:
      | sku   | name-fr_FR | family |
      | jeans | Nice jeans | pants  |
    And I am logged in as "Julia"
    When I edit the "jeans" product
    And I fill in the following information:
      | Name | Really nice jeans |
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I visit the "Attributes" tab
    And I visit the "Marketing" group
    And the product "jeans" should have the following values:
      | price      |            |
      | name-fr_FR | Nice jeans |

  @jira https://akeneo.atlassian.net/browse/PIM-3301
  Scenario: Successfully revert a product date and leave it empty
    And the following product:
      | sku   | family  |
      | jeans | jackets |
    And I am logged in as "Julia"
    When I edit the "jeans" product
    And I change the "mobile Release date" to "2014-05-20"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I save the product
    And the product "jeans" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully revert a product number and leave it empty
    And the following product:
      | sku   | family  |
      | jeans | jackets |
    And I am logged in as "Julia"
    When I edit the "jeans" product
    And I visit the "Marketing" group
    And I change the "mobile Number in stock" to "100"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I save the product
    And the product "jeans" should have the following values:
      | number_in_stock-mobile |  |
