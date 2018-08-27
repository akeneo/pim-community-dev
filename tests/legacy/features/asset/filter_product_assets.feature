@javascript
Feature: Filter product assets
  In order to easily manage product assets
  As an asset manager
  I need to be able to filter product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the assets grid

  Scenario Outline: Successfully filter product assets
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter      | operator         | value                     | result                                                              | count |
      | tags        | in list          | women                     | mouette                                                             | 1     |
      | tags        | in list          | lacework, men             | eagle, minivan                                                      | 2     |
      | tags        | is empty         |                           | photo                                                               | 1     |
      | endOfUseAt  | between          | 01/01/2006 and 01/01/2008 | paint, dog                                                          | 2     |
      | endOfUseAt  | more than        | 09/01/2015                | autumn, tiger                                                       | 2     |
      | endOfUseAt  | less than        | 01/01/2030                | dog, autumn, paint, akene                                           | 4     |
      | description | contains         | animal                    | dog, mouette                                                        | 2     |
      | description | does not contain | water                     | paint, chicagoskyline, akene, dog, machine, minivan, mouette, tiger | 8     |

  Scenario: Successfully search on code
    When I search "ma"
    Then the grid should contain 2 elements
    And I should see entities man_wall and machine

  Scenario: Successfully filter product assets by category
    When I select the "Asset main catalog" tree
    Then the grid should contain 15 elements
    When I open the category tree
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
