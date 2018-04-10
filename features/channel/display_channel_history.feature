Feature: Display the channel history
  In order to know who, when and what changes has been made to a channel
  As an administrator
  I need to have access to a channel history

  @javascript
  Scenario: Successfully edit a channel and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the channel creation page
    And I fill in the following information:
      | Code                    | foo             |
      | English (United States) | bar             |
      | Category tree           | 2014 collection |
      | Currencies              | EUR             |
      | Locales                 | French (France) |
    And I press the "Save" button
    Then I should be redirected to the "foo" channel page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property    | value           |
      | 1       | code        | foo             |
      | 1       | label-en_US | bar             |
      | 1       | category    | 2014_collection |
      | 1       | currencies  | EUR             |
      | 1       | locales     | fr_FR           |

  @javascript @jira https://akeneo.atlassian.net/browse/PIM-7279
  Scenario: Prevent javascript execution from history tab while updating channel label translations
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the "ecommerce" channel page
    And I fill in the following information:
      | English (United States) | <script>document.getElementById('top-page').classList.add('foo');</script> |
    And I save the channel
    Then I should see the flash message "Channel successfully updated."
    When I visit the "History" tab
    Then I should not see a "#top-page.foo" element
    And there should be 2 update
    And I should see "label-en_US: <script>document.getElementById('top-page').classList.add('foo');</script>"
