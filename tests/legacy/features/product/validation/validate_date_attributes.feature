@javascript
Feature: Validate date attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for date attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type             | scopable | unique | date_min   | date_max   | group |
      | release   | Release     | pim_catalog_date | 0        | 1      | 2013-01-01 | 2015-12-12 | other |
      | available | Available   | pim_catalog_date | 1        | 0      | 2013-01-01 | 2015-12-12 | other |
    And the following family:
      | code | label-en_US | attributes            | requirements-ecommerce | requirements-mobile |
      | baz  | Baz         | sku,release,available | sku                    | sku                 |
    And the following products:
      | sku | family |
      | foo | baz    |
      | bar | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the unique constraint of date attribute
    Given I change the Release to "01/01/2013"
    And I save the product
    When I am on the "bar" product page
    And I change the Release to "01/01/2013"
    And I save the product
    Then I should see validation tooltip "The value 2013-01-01 is already set on another product for the unique attribute release"
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date min constraint of date attribute
    Given I change the Release to "01/01/2011"
    And I save the product
    Then I should see validation tooltip "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date min constraint of scopable date attribute
    Given I change the Available to "01/01/2012"
    And I save the product
    Then I should see validation tooltip "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date max constraint of date attribute
    Given I change the Release to "01/01/2016"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-4216
  Scenario: Validate the date max constraint of scopable date attribute
    Given I change the Available to "03/03/2017"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Other" tab
