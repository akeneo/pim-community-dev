Feature: Join a document to a product
  In order to join a document to a product
  As a regular user
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label       | type | allowed extensions |
      | Description | file | txt                |
    And a "Car" product
    And the "Car" product has the "description" attribute
    And I am logged in as "Mary"
    And I am on the "Car" product page

  Scenario: Succesfully leave the document empty
    When I save the product
    Then I should see "Product successfully updated"

  Scenario: Succesfully upload a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    Then I should see "akeneo.txt"

  @javascript
  Scenario: Succesfully display the document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I open "akeneo.txt" in the current window
    Then I should see the "akeneo.txt" content

  @javascript
  Scenario: Successfully remove a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I remove the "Description" file
    And I save the product
    Then I should not see "akeneo.txt"

  @javascript
  Scenario: Successfully replace a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I remove the "Description" file
    When I attach file "akeneo2.txt" to "Description"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see "akeneo2.txt"

  @javascript
  Scenario: Successfully replace and remove a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I remove the "Description" file
    And I attach file "akeneo2.txt" to "Description"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see "akeneo2.txt"
