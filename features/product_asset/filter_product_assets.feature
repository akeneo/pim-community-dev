@javascript
Feature: Filter product assets
  In order to easily manage product assets
  As an asset manager
  I need to be able to filter product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the assets page

  Scenario: Successfully filter product assets
    And I should be able to use the following filters:
      | filter      | operator         | value                     | result                                                              |
      | code        | contains         | ma                        | man_wall, machine                                                   |
      | code        | is equal to      | bridge                    | bridge                                                              |
      | tags        | in list          | women                     | mouette                                                             |
      | tags        | in list          | lacework, men             | eagle, minivan                                                      |
      | tags        | is empty         |                           | photo                                                               |
      | endOfUseAt  | between          | 01/01/2006 and 01/01/2008 | paint, dog                                                          |
      | endOfUseAt  | more than        | 09/01/2015                | autumn, tiger                                                       |
      | endOfUseAt  | less than        | 01/01/2030                | dog, autumn, paint, akene                                           |
      | description | contains         | animal                    | dog, mouette                                                        |
      | description | does not contain | water                     | paint, chicagoskyline, akene, dog, machine, minivan, mouette, tiger |

  Scenario: Successfully filter product assets by category
    When I select the "Asset main catalog" tree
    Then the grid should contain 15 elements
    When I uncheck the "Include sub-categories" switch
    And I expand the "images" category
    Then I should be able to use the following filters:
      | filter   | operator | value  | result                                          |
      | category |          | images | paint, chicagoskyline, akene, autumn and bridge |
      | category |          | other  | autumn, bridge, dog, eagle and machine          |
      | category |          | situ   | paint, man_wall, minivan, mouette and mountain  |
    When I check the "Include sub-categories" switch
    Then I should be able to use the following filters:
      | filter   | operator | value  | result                                                                                                     |
      | category |          | images | paint, chicagoskyline, akene, autumn, bridge, dog, eagle, machine, man_wall, minivan, mouette and mountain |
    When I filter by "category" with operator "unclassified" and value ""
    Then I should see assets mugs, photo and tiger
