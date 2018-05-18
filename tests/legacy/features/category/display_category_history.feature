@javascript
Feature: Display the category history
  In order to know who, when and what changes has been made to a category
  As a product manager
  I need to have access to a category history

  @skip-doc @ce
  Scenario: Display category updates
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the category tree creation page
    When I fill in the following information:
      | Code                    | book          |
      | English (United States) | Book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property    | value         | date |
      | 1       | code        | book          | now  |
      | 1       | label-en_US | Book category | now  |
    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | My book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value            | date |
      | 1       | code        | book             | now  |
      | 1       | label-en_US | Book category    | now  |
      | 2       | label-en_US | My book category | now  |

  @ce @jira https://akeneo.atlassian.net/browse/PIM-7279
  Scenario: Prevent javascript execution from history tab while updating category label translations
    Given a "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the "default" category page
    And I fill in the following information:
      | English (United States) | <script>document.getElementById('top-page').classList.add('foo');</script> |
    And I save the category
    Then I should see the flash message "Category successfully updated."
    When I visit the "History" tab
    Then I should not see a "#top-page.foo" element
    And I should see history:
      | version | property    | value                                                                                 | date |
      | 2       | label-en_US | \<script\>document\.getElementById\('top-page'\)\.classList\.add\('foo'\);\</script\> | now  |
