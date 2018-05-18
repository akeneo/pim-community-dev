@javascript
Feature: Validate unique attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for unique attribute

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | label-en_US | type               | unique | group | decimals_allowed | negative_allowed |
      | text   | Text        | pim_catalog_text   | 1      | other |                  |                  |
      | number | Number      | pim_catalog_number | 1      | other | 0                | 0                |
      | date   | Date        | pim_catalog_date   | 1      | other |                  |                  |
    And the following families:
      | code        | label-en_US | attributes | requirements-ecommerce | requirements-mobile |
      | with_text   | With Text   | sku,text   | sku                    | sku                 |
      | with_number | With Number | sku,number | sku                    | sku                 |
      | with_date   | With Date   | sku,date   | sku                    | sku                 |
    And the following products:
      | sku     | family      |
      | text1   | with_text   |
      | text2   | with_text   |
      | number1 | with_number |
      | number2 | with_number |
      | date1   | with_date   |
      | date2   | with_date   |
    And I am logged in as "Mary"

  Scenario: Validate the unique constraint of text attribute with a provided text
    Given I am on the "text1" product page
    And I change the Text to "my-text"
    And I save the product
    When I am on the "text2" product page
    And I change the Text to "my-text"
    And I save the product
    Then I should see validation tooltip "The value my-text is already set on another product for the unique attribute text"
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-3961
  Scenario: Validate the unique constraint of text attribute with an empty text
    Given I am on the "text1" product page
    And I change the Text to ""
    And I save the product
    When I am on the "text2" product page
    And I change the Text to ""
    And I save the product
    Then I should not see validation tooltip "The value  is already set on another product for the unique attribute text"

  @jira https://akeneo.atlassian.net/browse/PIM-7323
  Scenario: Validate the unique constraint of text attribute with an removed value
    Given I am on the "text1" product page
    And I change the Text to "my-text"
    And I save the product
    Given I am on the "text1" product page
    And I change the Text to ""
    And I save the product
    When I am on the "text2" product page
    And I change the Text to "my-text"
    And I save the product
    Then I should not see validation tooltip "The value my-text is already set on another product for the unique attribute text"

  Scenario: Validate the unique constraint of number attribute with a provided number greater than 0
    Given I am on the "number1" product page
    And I change the Number to "12"
    And I save the product
    When I am on the "number2" product page
    And I change the Number to "12"
    And I save the product
    And I should see validation tooltip "The value 12 is already set on another product for the unique attribute number"
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-3961
  Scenario: Validate the unique constraint of number attribute with an empty number
    Given I am on the "number1" product page
    And I change the Number to ""
    And I save the product
    When I am on the "number2" product page
    And I change the Number to ""
    And I save the product
    Then I should not see validation tooltip "The value  is already set on another product for the unique attribute number"

  @skip @info date picker does not work properly on CI
  Scenario: Validate the unique constraint of date attribute with a provided date
    Given the following product values:
      | product | attribute | value      |
      | postit  | date      | 2015-01-01 |
    Given I am on the "date2" product page
    And I change the Date to "2015/01/01"
    And I save the product
    And I should see validation tooltip "The value 2015-01-01 is already set on another product for the unique attribute date"
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-3961
  Scenario: Validate the unique constraint of date attribute with an empty date
    Given I am on the "date1" product page
    And I save the product
    When I am on the "date2" product page
    And I save the product
    Then I should not see validation tooltip "The value  is already set on another product for the unique attribute date1"
