@javascript
Feature: Define the attribute requirement
  In order to ensure product completness when exporting them
  As a user
  I need to be able to define which attributes are required or not for a given channel

  Background:
    Given the following family:
      | code       |
      | Smartphone |
    And the following product attributes:
      | label       | family     |
      | Description | Smartphone |
      | Name        | Smartphone |
    And I am logged in as "admin"
    And I am on the "Smartphone" family page

  Scenario: Succesfully display all the channel attribute requirements
    Given I visit the "Attributes" tab
    Then attribute "Description" should not be required in channels Mobile and Ecommerce
    And attribute "Name" should not be required in channels Mobile and Ecommerce

  Scenario: Succesfully make an attribute required for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "Description" requirement in channel "Mobile"
    And I save the family
    And I visit the "Attributes" tab
    Then attribute "Description" should be required in channel Mobile
    But attribute "Description" should not be required in channel Ecommerce
    And attribute "Name" should not be required in channels Mobile and Ecommerce
