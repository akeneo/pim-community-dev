@javascript
Feature: Export assets
  In order to be able to access and modify asset data outside PIM
  As a product manager
  I need to be able to import and export assets

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4784
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
    code;description;enabled;end_of_use;tags;categories
    paint;Photo of a paint.;1;2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images,situ
    chicagoskyline;This is chicago!;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
    akene;Because Akeneo;1;2015-08-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
    autumn;Leaves and water;1;2015-12-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;autre,images
    bridge;Architectural bridge of a city, above water;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;autre,images
    dog;Obviously not a cat, but still an animal;1;2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;autre
    eagle;;1;;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage;autre
    machine;A big machine;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;autre
    man_wall;;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;situ
    minivan;My car;1;;backless,big_sizes,dress_suit,flower,lacework,men,neckline,pattern,pea,solid_color,stripes,vintage;situ
    mouette;Majestic animal;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage,women;situ
    mountain;;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;situ
    mugs;;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;
    photo;;1;;;
    tiger;Tiger of bengal, taken by J. Josh;1;2050-01-25;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;
    """
