Feature: Edit a family
  In order to provide accurate information about a family
  As a user
  I need to be able to edit its code and the translations of its name

  Background:
    Given the following families:
      | code       |
      | smartphone |
      | bags       |
      | jewels     |
    And I am logged in as "admin"
    When I am on the families page

  Scenario: Successfully edit a family
    Given I edit the "Bags" family
    When I change the Code to "purse"
    And I save the family
    Then I should see "Family successfully updated"

  Scenario: Fail to set an already used code
    Given I edit the "Bags" family
    When I change the Code to "smartphone"
    And I save the family
    Then I should see a tooltip "This value is already used."

  Scenario: Fail to set a non-valid code
    Given I edit the "Bags" family
    When I change the Code to an invalid value
    And I save the family
    Then I should see a tooltip "Family code may contain only letters, numbers and underscores"

  Scenario: Successfully set the translations of the name
    Given I am on the "Jewels" family page
    And I change the english Label to "NewJewelery"
    And I save the family
    Then I should see the families [bags], [smartphone] and NewJewelery

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "Jewels" family page
    When I change the english Label to "NewJewelery"
    Then I should see "There are unsaved changes."

  @javascript
  Scenario: Successfully have a confirmation popup when I change page with unsaved changes
    Given I am on the "Jewels" family page
    When I change the english Label to "NewJewelery"
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                  |
      | content | You will lose changes to the family if you leave the page. |
