@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "default" catalog configuration
    And I add the "english" locale to the "mobile" channel
    And the following attributes:
      | code        | type     | localizable | wysiwyg_enabled | label       | scopable |
      | description | textarea | yes         | yes             | Description | yes      |
      | name        | text     | no          |                 | Name        | no       |
      | other_name  | text     | yes         |                 | Other Name  | yes      |
    And the following products:
      | sku    |
      | sandal |
    And the following product values:
      | product | attribute   | value                                | locale | scope     |
      | sandal  | description | My awesome description for ecommerce | en_US  | ecommerce |
      | sandal  | description | My awesome description for mobile    | en_US  | mobile    |
      | sandal  | other_name  | My awesome sandals                   | en_US  | ecommerce |
      | sandal  | other_name  | My awesome sandals for mobile        | en_US  | mobile    |
      | sandal  | name        | My sandals name                      |        |           |

  Scenario: Successfully create, edit and save a product
    Given I am logged in as "Mary"
    And I am on the "sandal" product page
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

  @jira https://akeneo.atlassian.net/browse/PIM-3615
  Scenario: Successfully edit a product description, and have attributes set to the default scope (For Sandra => mobile and Julia => ecommerce).
    Given I am logged in as "Sandra"
    And I am on the "sandal" product page
    And the english mobile other_name of "sandal" should be "My awesome sandals for mobile"
    Then I logout
    And I am logged in as "Julia"
    When I am on the "sandal" product page
    Then the english ecommerce other_name of "sandal" should be "My awesome sandals"

  @skip-pef
  # Working well in application but scenario fails because context is kept only in regular hash navigation, behat
  # reloads the completely the page
  Scenario: Successfully preserve channel filter between datagrid and edit form
    Given I am logged in as "Sandra"
    And I am on the "sandal" product page
    And I switch the scope to "mobile"
    Then the product Description should be "My awesome description for mobile"
    When I am on the products page
    And I filter by "Channel" with value "E-Commerce"
    When I am on the "sandal" product page
    Then the product Description should be "My awesome description for ecommerce"
