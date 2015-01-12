@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "default" catalog configuration
    And I am logged in as "Mary"
    And the following products:
      | sku    |
      | sandal |
    And the following attributes:
      | code        | type     | localizable | availableLocales | wysiwyg_enabled | label-en_US |
      | description | textarea | yes         | en_US            | yes             | Description |
      | name        | text     | yes         |                  |                 | Name        |
    And the following product values:
      | product | attribute   | value                  | locale | scope     |
      | sandal  | description | My awesome description | en_US  | ecommerce |
      | sandal  | name        | My awesome sandals     | en_US  | ecommerce |

  Scenario: Successfully create, edit and save a product
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press the "Save" button
    Then I should be on the product "sandal" edit page
    Then the product Name should be "My Sandal"

  Scenario: Don't see the attributes tab when the user can't edit a product
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I remove rights to Edit attributes of a product
    And I save the role
    When I am on the "sandal" product page
    Then I should not see "Attributes"
    And I reset the "Administrator" rights

  @skip
  Scenario: Successfully edit a product description, and back to grid after save.
    Given I am on the "sandal" product page
    And the english description of "sandal" should be "My awesome description"
    And I change the "Description" to "My new cool and awesome description"
    When I press "Save and back to grid" on the "Save" dropdown button
    Then I should be on the products page
    And I wait 3 seconds
    And the english description of "sandal" should be "My new cool and awesome description"
