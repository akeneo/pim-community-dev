Feature: Supplier Portal - Product File Dropping - Mark product file comments as read for a supplier

  Background:
    Given a supplier

  Scenario: A supplier contributor marks product file comments as read
    Given a product file with 50 retailer comments
    And supplier contributors have not read the comments yet
    When a supplier contributor marks all comments of a product file as read
    Then all retailer comments should be marked as read for this product file

  Scenario: A retailer marks product file comments as read
    Given a product file with 50 supplier comments
    And retailers have not read the comments yet
    When a retailer marks all comments of a product file as read
    Then all supplier comments should be marked as read for this product file
