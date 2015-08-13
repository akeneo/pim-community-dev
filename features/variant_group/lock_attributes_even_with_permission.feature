@javascript
Feature: Make the attribute unmodifiable if coming from a variant group, even if permission to edit
  In order to avoid variant group attribute value modification on products
  As a redactor
  I should not be able to modified a value coming from a variant group

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku              | groups     | size | main_color |
      | star_wars_jacket | hm_jackets | XS   | blue       |

  @jira https://akeneo.atlassian.net/browse/PIM-4477
  Scenario: I'm not able to remove a media if I have no permission on this attribute group
    Given the following attribute group accesses:
      | attribute group | user group | access |
      | media           | Redactor   | view   |
    And I am logged in as "Julia"
    And I am on the "hm_jackets" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Side view
    And I attach file "akeneo.jpg" to "Side view"
    And I save the variant group
    And I logout
    And I am logged in as "Mary"
    And I am on the "star_wars_jacket" product page
    And I visit the "Media" group
    Then I should not be able to remove the file of "Side view"

  @jira https://akeneo.atlassian.net/browse/PIM-4477
  Scenario: I'm not able to remove a media even if I have permission on this attribute group
    Given the following attribute group accesses:
      | attribute group | user group | access |
      | media           | Redactor   | edit   |
    And I am logged in as "Julia"
    And I am on the "hm_jackets" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Side view
    And I attach file "akeneo.jpg" to "Side view"
    And I save the variant group
    And I logout
    And I am logged in as "Mary"
    And I am on the "star_wars_jacket" product page
    And I visit the "Media" group
    Then I should not be able to remove the file of "Side view"
    But I add available attributes Top view
    And I attach file "akeneo.jpg" to "Top view"
    Then I should be able to remove the file of "Top view"
