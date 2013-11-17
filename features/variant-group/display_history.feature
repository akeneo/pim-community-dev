Feature: Display the variant group history
  In order to know who, when and what changes has been made to a variant group
  As a user
  I need to have access to a variant group history

  @javascript
  Scenario: Succesfully edit a variant group and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | converse_sneakers |
    And I select the axis "Size"
    And I press the "Save" button
    And I am on the variant groups page
    Then I should see groups converse_sneakers and caterpillar_boots
    When I am on the "converse_sneakers" variant group page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property   | value             |
      | create | 1       | code       | converse_sneakers |
      | create | 1       | type       | VARIANT           |
      | create | 1       | attributes | size              |
