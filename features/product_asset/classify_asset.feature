@javascript
Feature: Classify an asset in the trees I have access
  In order to classify assets
  As an asset manager
  I need to associate a asset to categories I have access

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Associate an asset to categories
    Given I edit the "mugs" asset
    When I visit the "Categories" tab
    And I should see "Asset main catalog 0"
    And I select the "Asset main catalog" tree
    And I expand the "Asset main catalog" category
    And I click on the "Sales documents" category
    And I click on the "Store documents" category
    And I press the "Save" button
    And I visit the "Categories" tab
    Then I should see "Asset main catalog 2"

  Scenario: Show only granted categories
    Given I edit the "mugs" asset
    When I visit the "Categories" tab
    And I select the "Asset main catalog" tree
    And I expand the "Asset main catalog" category
    Then I should not see "Technical documents"
    But I should see "Client documents"

  Scenario: See only granted trees
    Given the following assets categories:
      | code            | label-en_US     | parent          |
      | 2016_collection | 2016 Collection |                 |
      | 2016_images     | Images          | 2016_collection |
    And the following asset category accesses:
      | asset category   | user group | access |
      | 2016_collection  | IT support | view   |
      | images           | IT support | view   |
    When I edit the "mugs" asset
    And I visit the "Categories" tab
    Then I should see "Asset main catalog"
    But I should not see "2016 Collection"
    Then I logout
    And I am logged in as "Peter"
    And I edit the "mugs" asset
    And I visit the "Categories" tab
    Then I should see "Asset main catalog"
    And I should see "2016 Collection"

  Scenario: Remove permissions on categories tab on asset form
    Given I am logged in as "Peter"
    And removing the following permissions should hide the following section:
      | permission                         | section    | page          |
      | Consult the categories of an asset | Categories | "paint" asset |
