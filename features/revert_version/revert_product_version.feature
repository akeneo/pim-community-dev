@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  # what's tested here?
  # -----------------------------|-------------|-------------
  # TYPE                         | VALID VALUE | NULL VALUE
  # -----------------------------|-------------|-------------
  # pim_catalog_boolean          | done        | done
  # pim_catalog_date             | done        | done
  # pim_catalog_file             | -           | -
  # pim_catalog_identifier       | done        | N/A
  # pim_catalog_image            | -           | -
  # pim_catalog_metric           | done        | done
  # pim_catalog_multiselect      | done        | done
  # pim_catalog_number           | -           | done
  # pim_catalog_price_collection | done        | done
  # pim_catalog_simpleselect     | done        | done
  # pim_catalog_text             | done        | done
  # pim_catalog_textarea         | done        | done

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a product
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | shirt   |
    And I press the "Save" button in the popin
    And I wait to be on the "shirt" product page
    And I disable the product
    And I visit the "History" column tab
    When I revert the product version number 1
    Then product "shirt" should be enabled
    And I should see history in panel:
      | version | author      | property | value |
      | 3       | Julia Stark | enabled  | 1     |
      | 2       | Julia Stark | enabled  | 0     |
      | 1       | Julia Stark | SKU      | shirt |
      | 1       | Julia Stark | enabled  | 1     |

  Scenario: Successfully revert the status of a product (disabled)
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | shirt   |
    And I press the "Save" button in the popin
    And I wait to be on the "shirt" product page
    And I disable the product
    And the history of the product "shirt" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then product "shirt" should be enabled

  Scenario: Successfully revert the status of a product (enable)
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | shirt   |
    And I press the "Save" button in the popin
    And I wait to be on the "shirt" product page
    And I am on the "shirt" product page
    And I disable the product
    Then I should not see the text "There are unsaved changes."
    And I am on the "shirt" product page
    And I enable the product
    And the history of the product "shirt" has been built
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then product "shirt" should be disabled

  Scenario: Successfully revert the family of a product
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | jean  |
      | family | Pants |
    And I press the "Save" button in the popin
    And I wait to be on the "jean" product page
    And I am on the products grid
    Then I select rows jean
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change family" operation
    And I change the Family to "Jackets"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    Then the family of product "jean" should be "jackets"
    And I am on the "jean" product page
    And the history of the product "jean" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the family of product "jean" should be "pants"

  Scenario: Successfully revert the category of a product
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | sandals |
    And I press the "Save" button in the popin
    And I wait to be on the "sandals" product page
    And I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I click on the "summer_collection" category
    And I press the "Save" button
    And the history of the product "sandals" has been built
    Then I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then the category of "sandals" should be "winter_collection"

  @skip @jira https://akeneo.atlassian.net/browse/PIM-3765
  Scenario: Fail to revert attribute affected by a variant group
    Given the following product:
      | sku          | family  | size |
      | helly-hansen | Jackets | XS   |
    And the following variant groups:
      | code       | label-en_US          | axis | type    |
      | hh_jackets | Helly Hansen jackets | size | VARIANT |
    And the following variant group values:
      | group      | attribute | value | locale | scope |
      | hh_jackets | name      | a     | en_US  |       |
    Then I am on the "hh_jackets" variant group page
    Then the grid should contain 1 elements
    And I should see products helly-hansen
    And I check the row "helly-hansen"
    # TODO: see with @nidup => temporary fix (broken since the deferred explicit persist of Doctrine)
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    And the row "helly-hansen" should be checked
    Then I am on the "helly-hansen" product page
    And the history of the product "helly-hansen" has been built
    And I visit the "History" column tab
    When I revert the product version number 1
    Then I should see the flash message "Product can not be reverted because it belongs to a variant group"

  @skip-nav @jira https://akeneo.atlassian.net/browse/PIM-5796
  Scenario: Hide revert button if user cannot revert a product
    Given the following product:
      | sku     |
      | sandals |
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Restore a product
    And I save the role
    When I edit the "sandals" product
    And I add available attributes Name
    And I change the Name to "Sandal"
    And I press the "Save" button
    And the history of the product "sandals" has been built
    Then I visit the "History" column tab
    And I should see 2 versions in the history
    But I should not see the text "Restore"
