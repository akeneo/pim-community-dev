Feature: Export assets categories
  In order to be able to access and modify asset category data outside PIM
  As a product manager
  I need to be able to import and export assets categories

  @javascript
  Scenario: Successfully export assets categories
    Given a "clothing" catalog configuration
    And the following job "clothing_asset_category_export" configuration:
      | filePath | %tmp%/asset_category_export/asset_category_export.csv |
    And I am logged in as "Julia"
    And I am on the "clothing_asset_category_export" export job page
    When I launch the export job
    And I wait for the "clothing_asset_category_export" job to finish
    And I should see "read 54"
    And I should see "written 54"
    Then file "%tmp%/asset_category_export/asset_category_export.csv" should contain 55 rows
    Then exported file of "clothing_asset_category_export" should contain:
    """
    code;parent;label-de_DE;label-en_US;label-fr_FR
    asset_main_catalog;;;"Asset main catalog";"Catalogue principal d'Assets"
    images;asset_main_catalog;;images;images
    autre;images;;"Other picture";"Autre images"
    dos;images;;"Back picture";"image de dos"
    face;images;;"Front picture";"image de face"
    mont;images;;"User documentation";"documentation utilisateur"
    pack;images;;"Packaged picture";
    pres;images;;Presentation;Presentation
    sans;images;;"Unpackaged photo";
    situ;images;;"In situ picture";
    tech;images;;"Technical document";
    prioritized_images;asset_main_catalog;;"PRIORITISED IMAGES";"PRIORITISED IMAGES"
    other;prioritized_images;;"Other picture";
    back;prioritized_images;;"Back picture";
    front;prioritized_images;;"Front picture";
    pack2;prioritized_images;;"Packaged picture";
    unpacked;prioritized_images;;"Unpackaged photo";
    situ2;prioritized_images;;"In situ picture";
    print;asset_main_catalog;;"PRINT IMAGES";"PRINT IMAGES"
    3quart;print;;"Half side picture";
    otherP;print;;"Other print pictures";
    backP;print;;"Back picture";
    faceP;print;;"Front picture";
    packP;print;;"Packaged picture";
    picto;print;;Pictograms;Pictogrammes
    profil;print;;"Side picture";
    qrcode;print;;"Flash code";
    situP;print;;"In situ picture";
    videos;asset_main_catalog;;VIDEOS;VIDEOS
    instit;videos;;Ambiance;Ambiance
    montV;videos;;"User documentation";
    presV;videos;;Presentation;Presentation
    audio;asset_main_catalog;;AUDIO;AUDIO
    sound;audio;;Sound;Son
    client_documents;asset_main_catalog;;"CLIENT DOCUMENTS";"DOCUMENTS CLIENT"
    fab;client_documents;;"Vendor documentation";
    montage;client_documents;;"User documentation";
    notice;client_documents;;"User notice";
    store_documents;asset_main_catalog;;"STORE DOCUMENTS";"Documents Magasins"
    fabS;store_documents;;"Vendor documentation";
    montageS;store_documents;;"User documentation";
    sav;store_documents;;"After sales documentation";
    technical_documents;asset_main_catalog;;"TECHNICAL DOCUMENTS";"Documents Techniques"
    plv;technical_documents;;"PLV article";
    presse;technical_documents;;"Press Releases";
    promo;technical_documents;;"Promo Leaflet";
    radio;technical_documents;;"Radio commercial";
    web;technical_documents;;"Web highlight";
    sales_documents;asset_main_catalog;;"SALES DOCUMENTS";"Documents de vente"
    certif;sales_documents;;Certificates;Certificats
    control;sales_documents;;"Control Authorities";
    det;sales_documents;;"Detergent documentation";
    rappel;sales_documents;;Callback;
    retrait;sales_documents;;Withdrawal;
    """
