@javascript
Feature: Validate date attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for date attributes

  Background:
    Given a "clothing" catalog configuration
    And the following attributes:
      | code      | label-en_US | type | scopable | unique | date_min   | date_max   | group |
      | release   | Release     | date | no       | yes    | 2013-01-01 | 2015-12-12 | info  |
      | available | Available   | date | yes      | no     | 2013-01-01 | 2015-12-12 | info  |
    And the following family:
      | code | label-en_US | attributes              |
      | baz  | Baz         | sku, release, available |
    And the following products:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
      | bar | baz    | summer_collection |
#    And the following product values:
#      | product  | attribute | value      |
#      | bar      | release   | 2013-02-02 |
    And I am logged in as "Mary"
    And I am on the "foo" product page

#  Scenario: Validate the unique constraint of date attribute
#    Given I change the Release to "2013-02-02"
#    And I save the product
#    Then I should see validation tooltip "This value is already set on another product."
#    And I should see validation tooltip "There are errors in this tab!"
#    And the "Attributes" tab should be red

  Scenario: Validate the date min constraint of date attribute
    Given I change the Release to "01/01/2011"
    And I save the product
    Then I should see validation error "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the date min constraint of scopable date attribute
    Given I change the Available for scope tablet to "01/01/2012"
    And I save the product
    Then I should see validation tooltip "This date should be 2013-01-01 or after."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the date max constraint of date attribute
    Given I change the Release to "01/01/2016"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the date max constraint of scopable date attribute
    Given I change the Available for scope tablet to "03/03/2017"
    And I save the product
    Then I should see validation tooltip "This date should be 2015-12-12 or before."
    And there should be 1 error in the "Product information" tab
