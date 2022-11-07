Feature: Supplier Portal - Product File Dropping - Mark product file comments as read for a supplier

  Background:
    Given a supplier

  Scenario: A supplier contributor marks product file comments as read
    Given a product file with 50 retailer comments
    And supplier contributors have not read the comments yet
    When a supplier contributor marks all comments of a product file as read
    Then all retailer comments should be marked as read for this product file

  Scenario: A supplier contributor cannot mark product file comments as read if product file does not exist
    Given a product file
    When a supplier contributor try to mark comments of an unknown product file as read
    Then it should have throw an exception

  Scenario: A supplier contributor cannot marks product file comments as read if there is no comments on product file
    Given a product file
    When a supplier contributor marks all comments of a product file as read
    Then retailer comments shouldn't be marked as read for this product file

  Scenario: A retailer marks product file comments as read
    Given a product file with 50 supplier comments
    And retailers have not read the comments yet
    When a retailer marks all comments of a product file as read
    Then all supplier comments should be marked as read for this product file

  Scenario: A retailer cannot mark product file comments as read if product file does not exist
    Given a product file
    When a retailer try to mark comments of an unknown product file as read
    Then it should have throw an exception

  Scenario: A retailer cannot marks product file comments as read if there is no comments on product file
    Given a product file
    When a retailer marks all comments of a product file as read
    Then supplier comments shouldn't be marked as read for this product file
