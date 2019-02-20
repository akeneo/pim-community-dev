@javascript
Feature: Filter products per reference entity record
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per reference entity record

  Background:
    Given a "default" catalog configuration
    And a reference entity simple link attribute
    And a product with a value for this reference entity simple link attribute
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-8130
  Scenario: Successfully filter products by a single reference entity link attribute
    Given I am on the products grid
    And the grid should contain 1 elements
    Then I should be able to use the following filters:
      | filter | operator | value | result   |
      | brand  | in list  | ikea  | tabouret |
      | brand  | in list  | sony  |          |
