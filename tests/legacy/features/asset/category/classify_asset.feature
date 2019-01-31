@javascript
Feature: Classify an asset in the trees I have access
  In order to classify assets
  As an asset manager
  I need to associate a asset to categories I have access

  Background:
    Given the "clothing" catalog configuration

  Scenario: Associate an asset to categories
    Given I am logged in as "Pamela"
    And I edit the "mugs" asset
    When I visit the "Categories" tab
    And I should see the text "Asset main catalog 0"
    And I expand the "asset_main_catalog" category
    And I click on the "sales_documents" category
    And I click on the "store_documents" category
    And I press the "Save" button
    And I visit the "Categories" tab
    Then I should see the text "Asset main catalog 2"

  Scenario: Show only granted categories
    Given I am logged in as "Pamela"
    And I edit the "mugs" asset
    When I visit the "Categories" tab
    And I expand the "asset_main_catalog" category
    Then I should not see the text "Technical documents"
    But I should see the text "Client documents"

  Scenario: See only granted trees
    Given I am logged in as "Pamela"
    And the following assets categories:
      | code            | label-en_US     | parent          |
      | 2016_collection | 2016 Collection |                 |
      | 2016_images     | Images          | 2016_collection |
    And the following asset category accesses:
      | asset category  | user group | access |
      | 2016_collection | IT support | none   |
      | images          | IT support | view   |
    When I edit the "mugs" asset
    And I visit the "Categories" tab
    Then I should see the text "Asset main catalog"
    But I should not see the text "2016 Collection"
    Then I logout
    And I am logged in as "Peter"
    And I edit the "mugs" asset
    And I visit the "Categories" tab
    Then I should see the text "Asset main catalog"
    And I should not see the text "2016 Collection"

  Scenario: Remove permissions on categories tab on asset form
    Given I am logged in as "Peter"
    And I am on the "paint" asset page
    And I should see the text "Categories"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    When I revoke rights to resource Consult the categories of an asset
    And I save the role
    And I should not see the text "There are unsaved changes."
    And I am on the "paint" asset page
    Then I should not see the text "Categories"
