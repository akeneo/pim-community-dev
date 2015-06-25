@javascript
Feature: Filter product assets
  In order to easily manage product assets
  As a product manager
  I need to be able to filter product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully sort product assets
    And I should be able to use the following filters:
      | filter      | value                             | result                                                                                                      |
      | Code        | contains ma                       | man-wall, machine                                                                                           |
      | Code        | is equal to bridge                | bridge                                                                                                      |
      | Tags        | in list women                     | mouette                                                                                                     |
      | Tags        | in list lacework,men              | eagle, minivan                                                                                              |
      | Tags        | is empty                          | photo                                                                                                       |
      | End of use  | between 2006-01-01 and 2008-01-01 | paint, dog                                                                                                  |
      | End of use  | more than 2015-09-01              | autumn, tiger                                                                                               |
      | End of use  | less than 2030-01-01              | dog, autumn, paint, akene                                                                                   |
      | Description | contains animal                   | dog, mouette                                                                                                |
      | Description | does not contain water            | paint, chicagoskyline, akene, dog, eagle, machine, minivan, mouette, mountain, mugs, photo, tiger, man-wall |
