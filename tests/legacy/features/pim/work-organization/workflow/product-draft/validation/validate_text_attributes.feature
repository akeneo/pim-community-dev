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
    Then I should see validation error "The email attribute can not have the same value more than once. The bar@example.com value is already set on another product."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of text attribute
    Given I change the Barcode to "000000000"
    And I save the product
    Then I should see validation error "The barcode attribute must not contain more than 8 characters. The submitted value is too long."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable text attribute
    Given I change the Barcodes for scope mobile to "000000000"
    And I save the product
    Then I should see validation error "The barcodes attribute must not contain more than 8 characters. The submitted value is too long."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the email validation rule constraint of text attribute
    Given I change the Email to "foo"
    And I save the product
    Then I should see validation error "The email attribute requires an e-mail address."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the email validation rule constraint of scopable text attribute
    Given I change the Emails for scope mobile to "foo"
    And I save the product
    Then I should see validation error "The emails attribute requires an e-mail address."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the url validation rule constraint of text attribute
    Given I change the Link to "bar"
    And I save the product
    Then I should see validation error "The link attribute requires a url link."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the url validation rule constraint of scopable text attribute
    Given I change the Links for scope mobile to "bar"
    And I save the product
    Then I should see validation error "The links attribute requires a url link."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the regexp validation rule constraint of text attribute
    Given I change the Barcode to "111111"
    And I save the product
    Then I should see validation error "The barcode attribute must match the following regular expression: /^0\d*$/."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the regexp validation rule constraint of scopable text attribute
    Given I change the Barcodes for scope mobile to "111111"
    And I save the product
    Then I should see validation error "The barcodes attribute must match the following regular expression: /^0\d*$/."
    And there should be 1 error in the "Product information" tab
