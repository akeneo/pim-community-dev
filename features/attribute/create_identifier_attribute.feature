@javascript
Feature: Create an identifier attribute
  In order to have a unique identifier for each product
  As a user
  I need to create an identifier attribute

  Scenario: Successfully create an identifier attribute
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Identifier"
    Then I should see the Max characters, Validation rule and Searchable fields
    And the fields Unique, Scope, Usable as grid column and Usable as grid filter should be disabled
    And I fill in the following informations:
      | Name            | myId           |
      | Default         | Sku            |
      | Max characters  | 100            |
      | Description     | My identifier  |
      | Position        | 1              |
    And I press the "Save" button
    Then I should see "Attribute successfully saved"

  Scenario: Fail to create an identifier attribute
    Given the following product attribute:
      | label         | type       |
      | My identifier | identifier |
    And I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Identifier"
    Then I should see "An identifier attribute already exists"
    And I fill in the following informations:
      | Name            | mySecondId        |
      | Default         | Sku2              |
      | Max characters  | 100               |
      | Description     | My 2nd identifier |
      | Position        | 2                 |
    And I press the "Save" button
    Then I should see "An identifier attribute already exists"
