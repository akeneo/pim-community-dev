@javascript
Feature: Create an attribute
  In order to be able to define the properties of a product
  As a user
  I need to create a text attribute

  Scenario: Sucessfully create and validate a text attribute
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Text"
    And I fill in the following information:
     | Name | short_descsription |
    And I visit the "Values" tab
    And I fill in the following information:
     | Default     | Short description           |
     | Description | Short description attribute |
    And I save the attribute
    Then I should see "Attribute successfully created"

  Scenario: Fail to create a text attribute with an invalid code
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Text"
    When I change the Name to an invalid value
    And I save the attribute
    Then I should see "Attribute name may contain only letters, numbers and underscores"

  Scenario: Fail to create a text attribute with an invalid description
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Text"
    And I visit the "Values" tab
    When I change the Description to an invalid value
    And I save the attribute
    Then I should see "This value is too long"
