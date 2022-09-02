@javascript
Feature: Restore product is only available if the feature is enabled

  Scenario: Restore product is not available in the UI when feature is disabled
    Given a "footwear" catalog configuration
    And the following products:
      | sku       | family | heel_color | sole_fabric   |
      | red-heels | heels  | yellow     | neoprene,silk |
    And the following product values:
      | product     | attribute   | value        |
      | red-heels   | heel_color  | blue         |
      | red-heels   | sole_fabric | cashmerewool |
    And I am logged in as "Julia"
    And I am on the "red-heels" product page
    When I visit the "History" column tab
    And I should see 2 versions in the history
    But I should not see the text "Restore"
