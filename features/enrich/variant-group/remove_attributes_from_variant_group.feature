@javascript
Feature: Remove an attribute from a variant group
  In order to manage some attributes separately on variant group products
  As a product manager
  I need to be able to remove an attribute from a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku  | groups            | comment | color | size |
      | boot | caterpillar_boots | foo     | black | 40   |
    And I am logged in as "Julia"

  @skip @info Will be removed in PIM-6444
  Scenario: Successfully remove an attribute from a variant group
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attribute Comment
    And I visit the "Other" group
    Then I should see the Comment field
    When I save the variant group
    And I am on the "boot" product page
    And I visit the "Other" group
    Then I should see the Comment field
    And the field Comment should be disabled
    When I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    And I remove the "Comment" attribute
    And I confirm the deletion
    Then I should see the flash message "Attribute successfully removed from the variant group"
    And I should see available attribute Comment in group "Other"

  @skip @info Will be removed in PIM-6444 @jira https://akeneo.atlassian.net/browse/PIM-3697
  Scenario: Successfully remove an attribute from a variant group and ensure the field is enabled back
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attribute Comment
    And I save the variant group
    And I visit the "Other" group
    Then I should see the Comment field
    When I am on the "boot" product page
    And I visit the "Other" group
    Then the field Comment should be disabled
    And I should see that Comment is inherited from variant group attribute
    When I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    And I remove the "Comment" attribute
    And I confirm the deletion
    And I save the variant group
    And I am on the "boot" product page
    And I visit the "Other" group
    Then I should see that Comment is not inherited from variant group attribute
