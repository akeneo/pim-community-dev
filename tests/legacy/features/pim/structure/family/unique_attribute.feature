@javascript
Feature: Use an unique attribute
  In order to be able to use unique attribute at the right places
  As a product manager
  I need to be able to use unique attribute only in right places

  Background:
    Given the "footwear" catalog configuration
    And the following attribute:
      | code        | label-en_US      | type             | group | unique |
      | unique_attr | Unique attribute | pim_catalog_text | info  | 1      |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-6428
  Scenario: Successfully use unique attributes on family edit
    Given I am on the "boots" family page
    When I visit the "Attributes" tab
    Then I should see available attribute Unique attribute
    And I should see available attribute Handmade
