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
    attribute;code;label-en_US
    manufacturer;Converse;Converse
    manufacturer;TimberLand;TimberLand
    manufacturer;Nike;Nike
    manufacturer;Caterpillar;Caterpillar
    weather_conditions;dry;Dry
    weather_conditions;wet;Wet
    weather_conditions;hot;Hot
    weather_conditions;cold;Cold
    weather_conditions;snowy;Snowy
    rating;1;"1 star"
    rating;2;"2 stars"
    rating;3;"3 stars"
    rating;4;"4 stars"
    rating;5;"5 stars"
    size;35;35
    size;36;36
    size;37;37
    size;38;38
    size;39;39
    size;40;40
    size;41;41
    size;42;42
    size;43;43
    size;44;44
    size;45;45
    size;46;46
    color;white;White
    color;black;Black
    color;blue;Blue
    color;maroon;Maroon
    color;saddle;Saddle
    color;greem;Greem
    color;red;Red
    color;charcoal;Charcoal
    lace_color;laces_black;Black
    lace_color;laces_brown;Brown
    lace_color;laces_white;White

    """

