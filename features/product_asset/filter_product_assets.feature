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
      | filter      | value                             | result                                                                                                              |
      | Code        | contains ma                       | man-wall, machine                                                                                                   |
      | Code        | is equal to bridge                | bridge                                                                                                              |
      | Tags        | in list women                     | mouette                                                                                                             |
      | Tags        | in list lacework,men              | eagle, minivan                                                                                                      |
      | Tags        | is empty                          | photo                                                                                                               |
      | End of use  | between 2006-01-01 and 2008-01-01 | paint, dog                                                                                                          |
      | End of use  | more than 2015-09-01              | autumn, tiger                                                                                                       |
      | End of use  | less than 2030-01-01              | dog, autumn, paint, akene                                                                                           |
      | Description | contains animal                   | dog, mouette                                                                                                        |
      | Description | does not contain water            | paint, chicagoskyline, akene, dog, eagle, machine, minivan, mouette, mountain, mugs, photo, tiger, man-wall, winter |

  Scenario: Successfully filter product assets by category
    When I select the "Asset main catalog" tree
    Then the grid should contain 16 elements
    When I uncheck the "Include sub-categories" switch
    And I expand the "images" category
    Then I should be able to use the following filters:
      | filter         | value  | result                                          |
      | asset category | images | paint, chicagoskyline, akene, autumn and bridge |
      | asset category | autre  | autumn, bridge, dog, eagle and machine          |
      | asset category | situ   | paint, man-wall, minivan, mouette and mountain  |
    When I check the "Include sub-categories" switch
    Then I should be able to use the following filters:
      | filter         | value  | result                                                                                                     |
      | asset category | images | paint, chicagoskyline, akene, autumn, bridge, dog, eagle, machine, man-wall, minivan, mouette and mountain |
    When I filter by "asset category" with value "unclassified"
    Then I should see assets mugs, photo and tiger
