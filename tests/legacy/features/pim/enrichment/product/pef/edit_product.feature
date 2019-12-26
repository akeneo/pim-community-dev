@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "default" catalog configuration
    And I add the "english" locale to the "mobile" channel
    And the following attributes:
      | code        | type                 | localizable | wysiwyg_enabled | label-en_US | scopable | group |
      | description | pim_catalog_textarea | 1           | 1               | Description | 1        | other |
      | name        | pim_catalog_text     | 0           |                 | Name        | 0        | other |
      | other_name  | pim_catalog_text     | 1           |                 | Other Name  | 1        | other |
    And the following attributes:
      | code   | label-en_US | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | length | Shoes size  | pim_catalog_metric | Length        | CENTIMETER          | other | 0                | 0                |
    And the following products:
      | sku    |
      | sandal |
    And the following product values:
      | product | attribute   | value                                | locale | scope     |
      | sandal  | description | My awesome description for ecommerce | en_US  | ecommerce |
      | sandal  | description | My awesome description for mobile    | en_US  | mobile    |
      | sandal  | other_name  | My awesome sandals for ecommerce     | en_US  | ecommerce |
      | sandal  | other_name  | My awesome sandals for mobile        | en_US  | mobile    |
      | sandal  | name        | My sandals name                      |        |           |
      | sandal  | length      | 29 CENTIMETER                        |        |           |

  @critical @validate-migration
  Scenario: Successfully create, edit and save a product
    Given I am logged in as "Mary"
    And I am on the "sandal" product page
    And I visit the "All" group
    And I fill in the following information:
      | Name | My Sandal |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Name should be "My Sandal"

  @critical
  Scenario: Successfully updates the updated date of the product
    Given I am logged in as "Mary"
    And I am on the "sandal" product page
    And I set the updated date of the product "sandal" to "now - 10 days"
    Then the product "sandal" updated date should not be close to "now"
    When I fill in the following information:
      | Name | My edited Sandal |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product "sandal" updated date should be close to "now"

  Scenario: Don't see the attributes tab when the user can't edit a product
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Edit attributes of a product
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sandal" product page
    Then I should not see the text "Attributes"

  @critical
  Scenario: Successfully add a metric attribute to a product
    Given I am logged in as "Julia"
    And I am on the "sandal" product page
    When I change the "Shoes size" to "29 Dekameter"
    And I save the product
    Then the product Shoes size should be "29 Dekameter"

  @critical
  Scenario: Successfully switch the product scope
    And I am logged in as "Peter"
    When I am on the channel creation page
    And I fill in the following information:
      | Code                    | channel_code      |
      | English (United States) | The channel label |
      | Category tree           | Master catalog    |
      | Currencies              | EUR               |
      | Locales                 | French            |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I should be redirected to the "channel_code" channel page
    And I am on the "sandal" product page
    Then I switch the scope to "channel_code"
    And I should see the text "The channel label"

  @jira https://akeneo.atlassian.net/browse/PIM-6258
  Scenario: Successfully view a product even if permissions on locales channels and families are revoked
    Given I am logged in as "Peter"
    When I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to group Association types
    And I revoke rights to group Channels
    And I revoke rights to group Families
    And I revoke rights to group Locales
    And I save the role
    Then I should not see the text "There are unsaved changes."
    And I edit the "sandal" product
    Then I should see the text "Sandal"
