@javascript
Feature: Validate text attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for text attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code     | label-en_US | type | scopable | unique | max_characters | validation_rule | validation_regexp | group |
      | barcode  | Barcode     | text | no       | yes    | 8              | regexp          | /^0\d*$/          | info  |
      | email    | Email       | text | no       | yes    |                | email           |                   | info  |
      | link     | Link        | text | no       | no     |                | url             |                   | info  |
      | barcodes | Barcodes    | text | yes      | no     | 8              | regexp          | /^0\d*$/          | info  |
      | emails   | Emails      | text | yes      | no     |                | email           |                   | info  |
      | links    | Links       | text | yes      | no     |                | url             |                   | info  |
    And the following family:
      | code | label-en_US | attributes                                         |
      | baz  | Baz         | sku, barcode, email, link, barcodes, emails, links |
    And the following products:
      | sku | family | categories        | email           |
      | foo | baz    | summer_collection | foo@example.com |
      | bar | baz    | summer_collection | bar@example.com |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  @skip-pef
  Scenario: Validate the unique constraint of text attribute
    Given I change the Email to "bar@example.com"
    And I save the product
    Then I should see validation tooltip "The value bar@example.com is already set on another product for the unique attribute email"
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the max characters constraint of text attribute
    Given I change the Barcode to "000000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 8 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the max characters constraint of scopable text attribute
    Given I change the "mobile Barcodes" to "000000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 8 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the email validation rule constraint of text attribute
    Given I change the Email to "foo"
    And I save the product
    Then I should see validation tooltip "This value is not a valid email address."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the email validation rule constraint of scopable text attribute
    Given I change the "mobile Emails" to "foo"
    And I save the product
    Then I should see validation tooltip "This value is not a valid email address."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the url validation rule constraint of text attribute
    Given I change the Link to "bar"
    And I save the product
    Then I should see validation tooltip "This value is not a valid URL."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the url validation rule constraint of scopable text attribute
    Given I change the "mobile Links" to "bar"
    And I save the product
    Then I should see validation tooltip "This value is not a valid URL."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the regexp validation rule constraint of text attribute
    Given I change the Barcode to "111111"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  @skip-pef
  Scenario: Validate the regexp validation rule constraint of scopable text attribute
    Given I change the "mobile Barcodes" to "111111"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red
