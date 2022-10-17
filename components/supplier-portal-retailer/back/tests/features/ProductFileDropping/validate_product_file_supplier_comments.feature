Feature: Supplier Portal - Product File Dropping - A supplier comments a product file
  In order to collaborate with Julia
  As Jimmy
  I can comment a product file

  Background:
    Given a supplier

  Scenario: Validates that a comment cannot be empty
    Given a product file
    When a supplier comments it with " "
    Then I should have an error message telling that the comment should not be empty

  Scenario: Validates that a comment cannot exceed 255 characters
    Given a product file
    When a supplier comments it with a too long comment
    Then I should have an error message telling that the comment should not exceed 255 characters

  Scenario: Validates that we cannot have more than 50 comments on the same product file
    Given a product file with 50 comments
    When a supplier comments it with "Another comment, again!"
    Then I should have an error message telling that the product file cannot have more than 50 comments

  Scenario: A supplier can comment a product file
    Given a product file
    When a supplier comments it with "foo"
    Then the product file contains the supplier comment "foo"
