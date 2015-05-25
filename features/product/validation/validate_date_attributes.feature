@javascript @jira https://akeneo.atlassian.net/browse/PIM-4216
Feature: Validate date attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for date attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type | scopable | unique | date_min   | date_max   |
      | release   | Release     | date | no       | yes    | 2013-01-01 | 2015-12-12 |
      | available | Available   | date | yes      | no     | 2013-01-01 | 2015-12-12 |
    And the following family:
      | code | label-en_US | attributes              |
      | baz  | Baz         | sku, release, available |
    And the following products:
      | sku | family |
      | foo | baz    |
      | bar | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the unique constraint of date attribute
    Given I change the Release to "2013-02-02"
    And I save the product
    When I am on the "bar" product page
    And I change the Release to "2013-02-02"
    And I save the product
    Then I should see validation tooltip "This value is already set on another product."
    And there should be 1 error in the "Other" tab

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date min constraint of date attribute
    Given I change the Release to "2011-01-01"
    And I save the product
    Then I should see validation tooltip "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Other" tab

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date min constraint of scopable date attribute
    Given I switch the scope to "ecommerce"
    And I change the Available to "2012-01-01"
    And I save the product
    Then I should see validation tooltip "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Other" tab

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date max constraint of date attribute
    Given I change the Release to "2016-01-01"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Other" tab

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date max constraint of scopable date attribute
    Given I switch the scope to "ecommerce"
    And I change the Available to "2017-03-03"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Other" tab
