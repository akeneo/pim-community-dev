Feature: Export options
  In order to be able to access and modify options data outside PIM
  As a product manager
  I need to be able to export options

  @javascript
  Scenario: Successfully export options
    Given a "footwear" catalog configuration
    And the following job "footwear_option_export" configuration:
      | filePath | %tmp%/option_export/option_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_option_export" export job page
    When I launch the export job
    And I wait for the "footwear_option_export" job to finish
    Then exported file of "footwear_option_export" should contain:
    """
    attribute;code;default;label-en_US
    manufacturer;Converse;0;Converse
    manufacturer;TimberLand;0;TimberLand
    manufacturer;Nike;0;Nike
    manufacturer;Caterpillar;0;Caterpillar
    weather_conditions;dry;0;Dry
    weather_conditions;wet;0;Wet
    weather_conditions;hot;0;Hot
    weather_conditions;cold;0;Cold
    weather_conditions;snowy;0;Snowy
    rating;1;0;"1 star"
    rating;2;0;"2 stars"
    rating;3;0;"3 stars"
    rating;4;0;"4 stars"
    rating;5;0;"5 stars"
    size;35;0;35
    size;36;0;36
    size;37;0;37
    size;38;0;38
    size;39;0;39
    size;40;0;40
    size;41;0;41
    size;42;0;42
    size;43;0;43
    size;44;0;44
    size;45;0;45
    size;46;0;46
    color;white;0;White
    color;black;0;Black
    color;blue;0;Blue
    color;maroon;0;Maroon
    color;saddle;0;Saddle
    color;greem;0;Greem
    color;red;0;Red
    color;charcoal;0;Charcoal
    lace_color;laces_black;0;Black
    lace_color;laces_brown;0;Brown
    lace_color;laces_white;0;White

    """

