@javascript
Feature: Filter product assets
  In order to easily manage product assets
  As a product manager
  I need to be able to sort product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the product assets page

  Scenario: Successfully sort product assets
    And I should be able to sort the rows by code, description, status, end of use, created at and last updated at
