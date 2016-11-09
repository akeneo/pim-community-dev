@javascript
Feature: Export assets
  In order to be able to access and modify asset data outside PIM
  As a product manager
  I need to be able to import and export assets

  Scenario: Successfully export assets in CSV
    Given a "clothing" catalog configuration
    And the following job "csv_clothing_asset_export" configuration:
      | filePath | %tmp%/asset_export/asset_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_clothing_asset_export" export job page
    When I launch the export job
    And I wait for the "csv_clothing_asset_export" job to finish
    And I should see "read 15"
    And I should see "written 15"
    Then file "%tmp%/asset_export/asset_export.csv" should contain 16 rows
    Then exported file of "csv_clothing_asset_export" should contain:
    """
    code;categories;description;end_of_use;localized;tags
    paint;images,situ;"Photo of a paint.";2006-05-12;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    chicagoskyline;images;"This is chicago!";;1;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    akene;images;"Because Akeneo";2015-08-01;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    autumn;images,other;"Leaves and water";2015-12-01;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    bridge;images,other;"Architectural bridge of a city, above water";;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    dog;other;"Obviously not a cat, but still an animal";2006-05-12;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    eagle;other;;;0;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage
    machine;other;"A big machine";;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    man_wall;situ;;;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    minivan;situ;"My car";;0;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage
    mouette;situ;"Majestic animal";;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage,women
    mountain;situ;;;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    mugs;;;;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    photo;;;;0;
    tiger;;"Tiger of bengal, taken by J. Josh";2050-01-25;0;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage
    """

  Scenario: Successfully export assets in XLSX
    Given a "clothing" catalog configuration
    And the following job "xlsx_clothing_asset_export" configuration:
      | filePath | %tmp%/asset_export/asset_export.xlsx |
    And I am logged in as "Julia"
    And I am on the "xlsx_clothing_asset_export" export job page
    When I launch the export job
    And I wait for the "xlsx_clothing_asset_export" job to finish
    And I should see "read 15"
    And I should see "written 15"
    Then xlsx file "%tmp%/asset_export/asset_export.xlsx" should contain 16 rows
    Then exported xlsx file of "xlsx_clothing_asset_export" should contain:
      | code           | categories   | description                                 | end_of_use | localized | tags                                                                                               |
      | paint          | images,situ  | Photo of a paint.                           | 2006-05-12 | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | chicagoskyline | images       | This is chicago!                            |            | 1         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | akene          | images       | Because Akeneo                              | 2015-08-01 | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | autumn         | images,other | Leaves and water                            | 2015-12-01 | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | bridge         | images,other | Architectural bridge of a city, above water |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | dog            | other        | Obviously not a cat, but still an animal    | 2006-05-12 | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | eagle          | other        |                                             |            | 0         | backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage |
      | machine        | other        | A big machine                               |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | man_wall       | situ         |                                             |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | minivan        | situ         | My car                                      |            | 0         | backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage |
      | mouette        | situ         | Majestic animal                             |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage,women        |
      | mountain       | situ         |                                             |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | mugs           |              |                                             |            | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
      | photo          |              |                                             |            | 0         |                                                                                                    |
      | tiger          |              | Tiger of bengal, taken by J. Josh           | 2050-01-25 | 0         | backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage              |
