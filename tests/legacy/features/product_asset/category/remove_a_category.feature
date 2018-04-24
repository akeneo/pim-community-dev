@javascript
Feature: Remove an asset category
  In order to be able to remove an unused category
  As an asset manager
  I need to be able to remove a category

  Background:
    Given a "clothing" catalog configuration
    And the following assets:
      | code   | categories                       |
      | logo_1 | images, print                    |
      | cgv    | store_documents, sales_documents |
    And I am logged in as "Pamela"

  Scenario: Remove a simple asset category
    Given I am on the "situ" asset category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the asset category "images" edit page
    And I should see the flash message "Category successfully removed"
    And I should not see the "situ" category under the "images" category

  Scenario: Remove an asset category with sub-categories
    Given I am on the "videos" asset category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the asset category "asset_main_catalog" edit page
    And I should see the flash message "Category successfully removed"
    And I should not see "Videos"

  Scenario: Remove an asset category with products linked
    Given I am on the "store_documents" asset category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the asset category "asset_main_catalog" edit page
    And I should see the flash message "Category successfully removed"
    Then I should not see "Store documents"
    When I edit the "cgv" asset
    Then asset category of "cgv" should be "sales_documents"

  Scenario: Remove an asset category with sub-categories and products linked
    Given I am on the "images" asset category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the asset category "asset_main_catalog" edit page
    And I should see the flash message "Category successfully removed"
    Then I should not see "Images"
    And I should not see "Other picture"
    And I should not see "In situ pictures"
    And I should not see "Prioritised images"
    When I edit the "logo_1" asset
    Then asset category of "logo_1" should be "print"

  Scenario: Remove an asset category tree
    Given the following assets category:
      | code            | parent | label-en_US     |
      | 2013_collection |        | 2013 collection |
    And I am on the "2013_collection" asset category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be redirected on the asset category tree creation page
    And I should see the flash message "Tree successfully removed"

  Scenario: Cancel the removal of an asset category
    Given I am on the "images" asset category page
    When I press the secondary action "Delete"
    And I cancel the deletion
    Then I should see the "images" category under the "asset_main_catalog" category

  @jira https://akeneo.atlassian.net/browse/PIM-4227
  Scenario: Remove an asset category with linked products limit exceeded
    Given the following assets:
      | code     | categories |
      | video_1  | videos     |
      | video_2  | videos     |
      | video_3  | videos     |
      | video_4  | videos     |
      | video_5  | videos     |
      | video_6  | videos     |
      | video_7  | videos     |
      | video_8  | videos     |
      | video_9  | videos     |
      | video_10 | videos     |
      | video_11 | videos     |
      | video_12 | videos     |
      | video_13 | videos     |
      | video_14 | videos     |
      | video_15 | videos     |
      | video_16 | videos     |
      | video_17 | videos     |
      | video_18 | videos     |
      | video_19 | videos     |
      | video_20 | videos     |
      | video_21 | videos     |
    And I am on the "videos" asset category page
    When I press the secondary action "Delete"
    Then I should see a confirm dialog with the following content:
      | title   | Delete confirmation                                                                        |
      | content | This category contains more products than allowed for this operation (20 products maximum) |

  Scenario: Remove an asset category with linked products limit exceeded including sub-categories
    Given the following assets:
      | code     | categories         |
      | video_1  | prioritized_videos |
      | video_2  | prioritized_videos |
      | video_3  | prioritized_videos |
      | video_4  | prioritized_videos |
      | video_5  | prioritized_videos |
      | video_6  | prioritized_videos |
      | video_7  | prioritized_videos |
      | video_8  | prioritized_videos |
      | video_9  | prioritized_videos |
      | video_10 | prioritized_videos |
      | video_11 | prioritized_videos |
      | video_12 | prioritized_videos |
      | video_13 | prioritized_videos |
      | video_14 | prioritized_videos |
      | video_15 | prioritized_videos |
      | video_16 | prioritized_videos |
      | video_17 | prioritized_videos |
      | video_18 | prioritized_videos |
      | video_19 | prioritized_videos |
      | video_20 | prioritized_videos |
      | video_21 | prioritized_videos |
    And I am on the "videos" asset category page
    When I press the secondary action "Delete"
    Then I should see a confirm dialog with the following content:
      | title   | Delete confirmation                                                                        |
      | content | This category contains more products than allowed for this operation (20 products maximum) |
