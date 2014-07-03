Feature: Display the channel colors
  In order to easily know the channel of scopable values
  As an administrator
  I need to be able to use colors for channels

  @javascript
  Scenario: Successfully display channel colors in the product edit form
    Given the "default" catalog configuration
    And the following channels:
      | code    | label   | color   | currencies | locales | tree    |
      | gray    | Gray    | gray    | EUR, USD   | en_US   | default |
      | white   | White   | white   | EUR, USD   | en_US   | default |
      | yellow  | Yellow  | yellow  | EUR, USD   | en_US   | default |
      | orange  | Orange  | orange  | EUR, USD   | en_US   | default |
      | magenta | Magenta | magenta | EUR, USD   | en_US   | default |
      | violet  | Violet  | violet  | EUR, USD   | en_US   | default |
      | blue    | Blue    | blue    | EUR, USD   | en_US   | default |
      | cyan    | Cyan    | cyan    | EUR, USD   | en_US   | default |
    And the following attribute:
      | code   | label  | type   | scopable |
      | mumber | Number | number | yes      |
    And a "foo" product
    And I am logged in as "Peter"
    And I edit the "foo" product
    And I add available attribute Number
    Then the scopable "Number" field should have the following colors:
      | scope   | background | font |
      | gray    | gray       | #111 |
      | white   | white      | #111 |
      | yellow  | yellow     | #fff |
      | orange  | orange     | #fff |
      | magenta | magenta    | #fff |
      | violet  | violet     | #fff |
      | blue    | blue       | #fff |
      | cyan    | cyan       | #fff |
