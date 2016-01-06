@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3760
  Scenario: Successfully revert a file attribute
   Given the following product:
   | sku     | family |
   | t-shirt | tees   |
   Given I am on the "t-shirt" product page
   And I add available attribute Datasheet
   And I visit the "Media" group
   And I attach file "bic-core-148.txt" to "Datasheet"
   And I visit the "Product information" group
   And I change the Name to "T-shirt with datasheet"
   And I save the product
   And I visit the "Media" group
   When I remove the "Datasheet" file
   And I save the product
   And the history of the product "t-shirt" has been built
   When I open the history
   Then I should see 3 versions in the history
   When I revert the product version number 2
   Then the product "t-shirt" should have the following values:
   | datasheet | bic-core-148.txt |
