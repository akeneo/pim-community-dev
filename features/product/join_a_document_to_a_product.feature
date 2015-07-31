@javascript
Feature: Join a document to a product
  In order to join a document to a product
  As a product manager
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label       | type | allowed extensions |
      | Description | file | txt                |
    And a "Car" product
    And the "Car" product has the "Description" attribute
    And I am logged in as "Julia"
    And I am on the "Car" product page

  @ce
  Scenario: Successfully leave the document empty
    When I save the product

  Scenario: Successfully upload a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    Then I should see the text "akeneo.txt"

  Scenario: Successfully remove a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I remove the "Description" file
    And I save the product
    Then I should not see "akeneo.txt"

  Scenario: Successfully replace a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    But I should see the text "akeneo.txt"
    And I remove the "Description" file
    When I attach file "akeneo2.txt" to "Description"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see the text "akeneo2.txt"

  Scenario: Successfully replace and remove a document
    When I attach file "akeneo.txt" to "Description"
    And I save the product
    And I remove the "Description" file
    And I attach file "akeneo2.txt" to "Description"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see the text "akeneo2.txt"
