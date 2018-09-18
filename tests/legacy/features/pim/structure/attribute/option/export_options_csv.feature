@javascript
Feature: Export options
  In order to be able to access and modify options data outside PIM
  As a product manager
  I need to be able to export options

  @critical
  Scenario: Successfully export options in CSV
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_option_export" configuration:
      | filePath | %tmp%/option_export/option_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_option_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_option_export" job to finish
    Then exported file of "csv_footwear_option_export" should contain:
    """
    attribute;code;sort_order;label-en_US
    manufacturer;Converse;1;Converse
    manufacturer;TimberLand;2;TimberLand
    manufacturer;Nike;3;Nike
    manufacturer;Caterpillar;4;Caterpillar
    weather_conditions;dry;1;Dry
    weather_conditions;wet;2;Wet
    weather_conditions;hot;3;Hot
    weather_conditions;cold;4;Cold
    weather_conditions;snowy;5;Snowy
    rating;1;1;"1 star"
    rating;2;2;"2 stars"
    rating;3;3;"3 stars"
    rating;4;4;"4 stars"
    rating;5;5;"5 stars"
    size;35;1;35
    size;36;2;36
    size;37;3;37
    size;38;4;38
    size;39;5;39
    size;40;6;40
    size;41;7;41
    size;42;8;42
    size;43;9;43
    size;44;10;44
    size;45;11;45
    size;46;12;46
    size;60;13;60
    color;white;1;White
    color;black;2;Black
    color;blue;3;Blue
    color;maroon;4;Maroon
    color;saddle;5;Saddle
    color;greem;6;Greem
    color;red;7;Red
    color;charcoal;8;Charcoal
    lace_color;laces_black;1;Black
    lace_color;laces_brown;2;Brown
    lace_color;laces_white;3;White
    """
