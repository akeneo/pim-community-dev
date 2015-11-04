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
    And the following attributes:
      | code        | label      | type   | metric family | default metric unit |
      | length      | Shoes size | metric | Length        | CENTIMETER          |
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
      | sandal  | length      | 29 CENTIMETER                        |        |           |

  Scenario: Successfully create, edit and save a product
    Given I am logged in as "Mary"
    And I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press the "Save" button
    Then I should be on the product "sandal" edit page
    Then the product Name should be "My Sandal"

  Scenario: Successfully updates the updated date of the product
    Given I am logged in as "Mary"
    And I am on the "sandal" product page
    And I set the updated date of the product "sandal" to "now - 10 days"
    Then the product "sandal" updated date should not be close to "now"
    When I fill in the following information:
      | Name | My edited Sandal |
    And I press the "Save" button
    And the product "sandal" updated date should be close to "now"

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

  # Working well in application but scenario fails
  @skip-pef
  Scenario: Successfully preserve channel filter between datagrid and edit form
    Given I am logged in as "Sandra"
    And I am on the "sandal" product page
    And I switch the scope to "mobile"
    Then the product Description should be "My awesome description for mobile"
    When I am on the products page
    And I filter by "Channel" with value "E-Commerce"
    When I am on the "sandal" product page
    Then the product Description should be "My awesome description for ecommerce"

  Scenario: Successfully add a metric attribute to a product
    Given I am logged in as "Julia"
    And I am on the "sandal" product page
    When I change the "Shoes size" to "29 DEKAMETER"
    And I save the product
    Then the product Shoes size should be "29 DEKAMETER"

  Scenario: Successfully switch the product scope
    And I am logged in as "Peter"
    When I am on the channel creation page
    And I fill in the following information:
      | Code          | channel_code      |
      | Default label | The channel label |
      | Category tree | Master catalog    |
      | Currencies    | EUR               |
      | Locales       | French            |
    And I press the "Save" button
    And I am on the "sandal" product page
    Then I switch the scope to "channel_code"
    And I should see the text "The channel label"
