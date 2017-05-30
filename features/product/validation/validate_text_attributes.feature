@javascript
Feature: Validate text attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for text attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code                | label-en_US         | type             | scopable | unique | max_characters | validation_rule | validation_regexp | group |
      | barcode             | Barcode             | pim_catalog_text | 0        | 1      | 8              | regexp          | /^0\d*$/          | other |
      | email               | Email               | pim_catalog_text | 0        | 1      |                | email           |                   | other |
      | link                | Link                | pim_catalog_text | 0        | 0      |                | url             |                   | other |
      | manufacturer_number | Manufacturer number | pim_catalog_text | 1        | 0      | 8              | regexp          | /^0\d*$/          | other |
      | recipient           | Recipient           | pim_catalog_text | 1        | 0      |                | email           |                   | other |
      | references          | References          | pim_catalog_text | 1        | 0      |                | url             |                   | other |
      | desc                | Description         | pim_catalog_text | 0        | 0      |                |                 |                   | other |
    And the following family:
      | code | label-en_US | attributes                                                           | requirements-ecommerce | requirements-mobile |
      | baz  | Baz         | sku,barcode,email,link,manufacturer_number,recipient,references,desc | sku                    | sku                 |
    And the following products:
      | sku | family |
      | foo | baz    |
      | bar | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the unique constraint of text attribute
    Given I change the Email to "bar@foo.com"
    And I save the product
    When I am on the "bar" product page
    And I change the Email to "bar@foo.com"
    And I save the product
    Then I should see validation tooltip "The value bar@foo.com is already set on another product for the unique attribute email"
    And there should be 1 error in the "Other" tab

  Scenario: Validate the max characters constraint of text attribute
    Given I change the Barcode to "000000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 8 characters or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the max characters constraint of scopable text attribute
    Given I switch the scope to "ecommerce"
    And I change the "Manufacturer number" to "000000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 8 characters or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the email validation rule constraint of text attribute
    Given I change the Email to "foo"
    And I save the product
    Then I should see validation tooltip "This value is not a valid email address."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the email validation rule constraint of scopable text attribute
    Given I switch the scope to "ecommerce"
    And I change the Recipient to "foo"
    And I save the product
    Then I should see validation tooltip "This value is not a valid email address."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the url validation rule constraint of text attribute
    Given I change the Link to "bar"
    And I save the product
    Then I should see validation tooltip "This value is not a valid URL."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the url validation rule constraint of scopable text attribute
    Given I switch the scope to "ecommerce"
    Given I change the References to "bar"
    And I save the product
    Then I should see validation tooltip "This value is not a valid URL."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the regexp validation rule constraint of text attribute
    Given I change the Barcode to "111111"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the regexp validation rule constraint of scopable text attribute
    Given I switch the scope to "ecommerce"
    Given I change the "Manufacturer number" to "111111"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-3447
  Scenario: Validate the max database value length of text attribute
    Given I change the Description to an invalid value
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 255 characters or less."
    And there should be 1 error in the "Other" tab
