@javascript
Feature: Export assets
  In order to be able to access and modify asset data outside PIM
  As a product manager
  I need to be able to import and export assets

  Scenario: Successfully export assets
    Given a "clothing" catalog configuration
    And the following job "clothing_asset_export" configuration:
      | filePath | %tmp%/asset_export/asset_export.csv |
    And I am logged in as "Julia"
    And I am on the "clothing_asset_export" export job page
    When I launch the export job
    And I wait for the "clothing_asset_export" job to finish
    And I should see "read 15"
    And I should see "written 15"
    Then file "%tmp%/asset_export/asset_export.csv" should contain 16 rows
    Then exported file of "clothing_asset_export" should contain:
    """
    code;localized;description;end_of_use;tags;categories
    paint;0;"Photo of a paint.";2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images,situ
    chicagoskyline;1;"This is chicago!";;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
    akene;0;"Because Akeneo";2015-08-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
    autumn;0;"Leaves and water";2015-12-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images,other
    bridge;0;"Architectural bridge of a city, above water";;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images,other
    dog;0;"Obviously not a cat, but still an animal";2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other
    eagle;0;;;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage;other
    machine;0;"A big machine";;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other
    man_wall;0;;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;situ
    minivan;0;"My car";;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage;situ
    mouette;0;"Majestic animal";;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage,women;situ
    mountain;0;;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;situ
    mugs;0;;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;
    photo;0;;;;
    tiger;0;"Tiger of bengal, taken by J. Josh";2050-01-25;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;
    """
