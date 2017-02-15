@javascript
Feature: Validate text attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for text attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code     | label-en_US | type             | scopable | unique | max_characters | validation_rule | validation_regexp | group |
      | barcode  | Barcode     | pim_catalog_text | 0        | 1      | 8              | regexp          | /^0\d*$/          | info  |
      | email    | Email       | pim_catalog_text | 0        | 1      |                | email           |                   | info  |
      | link     | Link        | pim_catalog_text | 0        | 0      |                | url             |                   | info  |
      | barcodes | Barcodes    | pim_catalog_text | 1        | 0      | 8              | regexp          | /^0\d*$/          | info  |
      | emails   | Emails      | pim_catalog_text | 1        | 0      |                | email           |                   | info  |
      | links    | Links       | pim_catalog_text | 1        | 0      |                | url             |                   | info  |
    And the following family:
      | code | label-en_US | attributes                                   |
      | baz  | Baz         | sku,barcode,email,link,barcodes,emails,links |
    And the following products:
      | sku | family | categories        | email           |
      | foo | baz    | summer_collection | foo@example.com |
      | bar | baz    | summer_collection | bar@example.com |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4641
  Scenario: Validate the unique constraint of text attribute
    Given I change the Email to "bar@example.com"
    And I save the product
    Then I should see validation error "The value bar@example.com is already set on another product for the unique attribute email"
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of text attribute
    Given I change the Barcode to "000000000"
    And I save the product
    Then I should see validation error "This value is too long. It should have 8 characters or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable text attribute
    Given I change the Barcodes for scope mobile to "000000000"
    And I save the product
    Then I should see validation error "This value is too long. It should have 8 characters or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the email validation rule constraint of text attribute
    Given I change the Email to "foo"
    And I save the product
    Then I should see validation error "This value is not a valid email address."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the email validation rule constraint of scopable text attribute
    Given I change the Emails for scope mobile to "foo"
    And I save the product
    Then I should see validation error "This value is not a valid email address."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the url validation rule constraint of text attribute
    Given I change the Link to "bar"
    And I save the product
    Then I should see validation error "This value is not a valid URL."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the url validation rule constraint of scopable text attribute
    Given I change the Links for scope mobile to "bar"
    And I save the product
    Then I should see validation error "This value is not a valid URL."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the regexp validation rule constraint of text attribute
    Given I change the Barcode to "111111"
    And I save the product
    Then I should see validation error "This value is not valid."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the regexp validation rule constraint of scopable text attribute
    Given I change the Barcodes for scope mobile to "111111"
    And I save the product
    Then I should see validation error "This value is not valid."
    And there should be 1 error in the "Product information" tab
