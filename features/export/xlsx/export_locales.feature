@javascript
Feature: Export locales
  In order to be able to access and modify locales data outside PIM
  As an administrator
  I need to be able to export locales in xlsx format

  Scenario: Successfully export locales in xlsx with headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_locale_export" configuration:
      | filePath | %tmp%/xlsx_footwear_locale_export/xlsx_footwear_locale_export.xlsx |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_locale_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_locale_export" job to finish
    Then exported xlsx file of "xlsx_footwear_locale_export" should contain:
      | code        | activated |
      | af_ZA       | 0         |
      | am_ET       | 0         |
      | ar_AE       | 0         |
      | ar_BH       | 0         |
      | ar_DZ       | 0         |
      | ar_EG       | 0         |
      | ar_IQ       | 0         |
      | ar_JO       | 0         |
      | ar_KW       | 0         |
      | ar_LB       | 0         |
      | ar_LY       | 0         |
      | ar_MA       | 0         |
      | arn_CL      | 0         |
      | ar_OM       | 0         |
      | ar_QA       | 0         |
      | ar_SA       | 0         |
      | ar_SY       | 0         |
      | ar_TN       | 0         |
      | ar_YE       | 0         |
      | as_IN       | 0         |
      | az_Cyrl_AZ  | 0         |
      | az_Latn_AZ  | 0         |
      | ba_RU       | 0         |
      | be_BY       | 0         |
      | bg_BG       | 0         |
      | bn_BD       | 0         |
      | bn_IN       | 0         |
      | bo_CN       | 0         |
      | br_FR       | 0         |
      | bs_Cyrl_BA  | 0         |
      | bs_Latn_BA  | 0         |
      | ca_ES       | 0         |
      | co_FR       | 0         |
      | cs_CZ       | 0         |
      | cy_GB       | 0         |
      | da_DK       | 0         |
      | de_AT       | 0         |
      | de_CH       | 0         |
      | de_DE       | 0         |
      | de_LI       | 0         |
      | de_LU       | 0         |
      | dsb_DE      | 0         |
      | dv_MV       | 0         |
      | el_GR       | 0         |
      | en_029      | 0         |
      | en_AU       | 0         |
      | en_BZ       | 0         |
      | en_CA       | 0         |
      | en_GB       | 0         |
      | en_IE       | 0         |
      | en_IN       | 0         |
      | en_JM       | 0         |
      | en_MY       | 0         |
      | en_NZ       | 0         |
      | en_PH       | 0         |
      | en_SG       | 0         |
      | en_TT       | 0         |
      | en_US       | 1         |
      | en_ZA       | 0         |
      | en_ZW       | 0         |
      | es_AR       | 0         |
      | es_BO       | 0         |
      | es_CL       | 0         |
      | es_CO       | 0         |
      | es_CR       | 0         |
      | es_DO       | 0         |
      | es_EC       | 0         |
      | es_ES       | 0         |
      | es_GT       | 0         |
      | es_HN       | 0         |
      | es_MX       | 0         |
      | es_NI       | 0         |
      | es_PA       | 0         |
      | es_PE       | 0         |
      | es_PR       | 0         |
      | es_PY       | 0         |
      | es_SV       | 0         |
      | es_US       | 0         |
      | es_UY       | 0         |
      | es_VE       | 0         |
      | et_EE       | 0         |
      | eu_ES       | 0         |
      | fa_IR       | 0         |
      | fi_FI       | 0         |
      | fil_PH      | 0         |
      | fo_FO       | 0         |
      | fr_BE       | 0         |
      | fr_CA       | 0         |
      | fr_CH       | 0         |
      | fr_FR       | 0         |
      | fr_LU       | 0         |
      | fr_MC       | 0         |
      | fy_NL       | 0         |
      | ga_IE       | 0         |
      | gd_GB       | 0         |
      | gl_ES       | 0         |
      | gsw_FR      | 0         |
      | gu_IN       | 0         |
      | ha_Latn_NG  | 0         |
      | he_IL       | 0         |
      | hi_IN       | 0         |
      | hr_BA       | 0         |
      | hr_HR       | 0         |
      | hsb_DE      | 0         |
      | hu_HU       | 0         |
      | hy_AM       | 0         |
      | id_ID       | 0         |
      | ig_NG       | 0         |
      | ii_CN       | 0         |
      | is_IS       | 0         |
      | it_CH       | 0         |
      | it_IT       | 0         |
      | iu_Cans_CA  | 0         |
      | iu_Latn_CA  | 0         |
      | ja_JP       | 0         |
      | ka_GE       | 0         |
      | kk_KZ       | 0         |
      | kl_GL       | 0         |
      | km_KH       | 0         |
      | kn_IN       | 0         |
      | kok_IN      | 0         |
      | ko_KR       | 0         |
      | ky_KG       | 0         |
      | lb_LU       | 0         |
      | lo_LA       | 0         |
      | lt_LT       | 0         |
      | lv_LV       | 0         |
      | mi_NZ       | 0         |
      | mk_MK       | 0         |
      | ml_IN       | 0         |
      | mn_MN       | 0         |
      | mn_Mong_CN  | 0         |
      | moh_CA      | 0         |
      | mr_IN       | 0         |
      | ms_BN       | 0         |
      | ms_MY       | 0         |
      | mt_MT       | 0         |
      | nb_NO       | 0         |
      | ne_NP       | 0         |
      | nl_BE       | 0         |
      | nl_NL       | 0         |
      | nn_NO       | 0         |
      | nso_ZA      | 0         |
      | oc_FR       | 0         |
      | or_IN       | 0         |
      | pa_IN       | 0         |
      | pl_PL       | 0         |
      | prs_AF      | 0         |
      | ps_AF       | 0         |
      | pt_BR       | 0         |
      | pt_PT       | 0         |
      | qut_GT      | 0         |
      | quz_BO      | 0         |
      | quz_EC      | 0         |
      | quz_PE      | 0         |
      | rm_CH       | 0         |
      | ro_RO       | 0         |
      | ru_RU       | 0         |
      | rw_RW       | 0         |
      | sah_RU      | 0         |
      | sa_IN       | 0         |
      | se_FI       | 0         |
      | se_NO       | 0         |
      | se_SE       | 0         |
      | si_LK       | 0         |
      | sk_SK       | 0         |
      | sl_SI       | 0         |
      | sma_NO      | 0         |
      | sma_SE      | 0         |
      | smj_NO      | 0         |
      | smj_SE      | 0         |
      | smn_FI      | 0         |
      | sms_FI      | 0         |
      | sq_AL       | 0         |
      | sr_Cyrl_BA  | 0         |
      | sr_Cyrl_CS  | 0         |
      | sr_Cyrl_ME  | 0         |
      | sr_Cyrl_RS  | 0         |
      | sr_Latn_BA  | 0         |
      | sr_Latn_CS  | 0         |
      | sr_Latn_ME  | 0         |
      | sr_Latn_RS  | 0         |
      | sv_FI       | 0         |
      | sv_SE       | 0         |
      | sw_KE       | 0         |
      | syr_SY      | 0         |
      | ta_IN       | 0         |
      | te_IN       | 0         |
      | tg_Cyrl_TJ  | 0         |
      | th_TH       | 0         |
      | tk_TM       | 0         |
      | tn_ZA       | 0         |
      | tr_TR       | 0         |
      | tt_RU       | 0         |
      | tzm_Latn_DZ | 0         |
      | ug_CN       | 0         |
      | uk_UA       | 0         |
      | ur_PK       | 0         |
      | uz_Cyrl_UZ  | 0         |
      | uz_Latn_UZ  | 0         |
      | vi_VN       | 0         |
      | wo_SN       | 0         |
      | xh_ZA       | 0         |
      | yo_NG       | 0         |
      | zh_CN       | 0         |
      | zh_HK       | 0         |
      | zh_MO       | 0         |
      | zh_SG       | 0         |
      | zh_TW       | 0         |
      | zu_ZA       | 0         |

  Scenario: Successfully export locales in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_locale_export" configuration:
      | filePath   | %tmp%/xlsx_footwear_locale_export/xlsx_footwear_locale_export.xlsx |
      | withHeader | no                                                                 |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_locale_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_locale_export" job to finish
    Then exported xlsx file of "xlsx_footwear_locale_export" should contain:
      | af_ZA       |0         |
      | am_ET       |0         |
      | ar_AE       |0         |
      | ar_BH       |0         |
      | ar_DZ       |0         |
      | ar_EG       |0         |
      | ar_IQ       |0         |
      | ar_JO       |0         |
      | ar_KW       |0         |
      | ar_LB       |0         |
      | ar_LY       |0         |
      | ar_MA       |0         |
      | arn_CL      |0         |
      | ar_OM       |0         |
      | ar_QA       |0         |
      | ar_SA       |0         |
      | ar_SY       |0         |
      | ar_TN       |0         |
      | ar_YE       |0         |
      | as_IN       |0         |
      | az_Cyrl_AZ  |0         |
      | az_Latn_AZ  |0         |
      | ba_RU       |0         |
      | be_BY       |0         |
      | bg_BG       |0         |
      | bn_BD       |0         |
      | bn_IN       |0         |
      | bo_CN       |0         |
      | br_FR       |0         |
      | bs_Cyrl_BA  |0         |
      | bs_Latn_BA  |0         |
      | ca_ES       |0         |
      | co_FR       |0         |
      | cs_CZ       |0         |
      | cy_GB       |0         |
      | da_DK       |0         |
      | de_AT       |0         |
      | de_CH       |0         |
      | de_DE       |0         |
      | de_LI       |0         |
      | de_LU       |0         |
      | dsb_DE      |0         |
      | dv_MV       |0         |
      | el_GR       |0         |
      | en_029      |0         |
      | en_AU       |0         |
      | en_BZ       |0         |
      | en_CA       |0         |
      | en_GB       |0         |
      | en_IE       |0         |
      | en_IN       |0         |
      | en_JM       |0         |
      | en_MY       |0         |
      | en_NZ       |0         |
      | en_PH       |0         |
      | en_SG       |0         |
      | en_TT       |0         |
      | en_US       |1         |
      | en_ZA       |0         |
      | en_ZW       |0         |
      | es_AR       |0         |
      | es_BO       |0         |
      | es_CL       |0         |
      | es_CO       |0         |
      | es_CR       |0         |
      | es_DO       |0         |
      | es_EC       |0         |
      | es_ES       |0         |
      | es_GT       |0         |
      | es_HN       |0         |
      | es_MX       |0         |
      | es_NI       |0         |
      | es_PA       |0         |
      | es_PE       |0         |
      | es_PR       |0         |
      | es_PY       |0         |
      | es_SV       |0         |
      | es_US       |0         |
      | es_UY       |0         |
      | es_VE       |0         |
      | et_EE       |0         |
      | eu_ES       |0         |
      | fa_IR       |0         |
      | fi_FI       |0         |
      | fil_PH      |0         |
      | fo_FO       |0         |
      | fr_BE       |0         |
      | fr_CA       |0         |
      | fr_CH       |0         |
      | fr_FR       |0         |
      | fr_LU       |0         |
      | fr_MC       |0         |
      | fy_NL       |0         |
      | ga_IE       |0         |
      | gd_GB       |0         |
      | gl_ES       |0         |
      | gsw_FR      |0         |
      | gu_IN       |0         |
      | ha_Latn_NG  |0         |
      | he_IL       |0         |
      | hi_IN       |0         |
      | hr_BA       |0         |
      | hr_HR       |0         |
      | hsb_DE      |0         |
      | hu_HU       |0         |
      | hy_AM       |0         |
      | id_ID       |0         |
      | ig_NG       |0         |
      | ii_CN       |0         |
      | is_IS       |0         |
      | it_CH       |0         |
      | it_IT       |0         |
      | iu_Cans_CA  |0         |
      | iu_Latn_CA  |0         |
      | ja_JP       |0         |
      | ka_GE       |0         |
      | kk_KZ       |0         |
      | kl_GL       |0         |
      | km_KH       |0         |
      | kn_IN       |0         |
      | kok_IN      |0         |
      | ko_KR       |0         |
      | ky_KG       |0         |
      | lb_LU       |0         |
      | lo_LA       |0         |
      | lt_LT       |0         |
      | lv_LV       |0         |
      | mi_NZ       |0         |
      | mk_MK       |0         |
      | ml_IN       |0         |
      | mn_MN       |0         |
      | mn_Mong_CN  |0         |
      | moh_CA      |0         |
      | mr_IN       |0         |
      | ms_BN       |0         |
      | ms_MY       |0         |
      | mt_MT       |0         |
      | nb_NO       |0         |
      | ne_NP       |0         |
      | nl_BE       |0         |
      | nl_NL       |0         |
      | nn_NO       |0         |
      | nso_ZA      |0         |
      | oc_FR       |0         |
      | or_IN       |0         |
      | pa_IN       |0         |
      | pl_PL       |0         |
      | prs_AF      |0         |
      | ps_AF       |0         |
      | pt_BR       |0         |
      | pt_PT       |0         |
      | qut_GT      |0         |
      | quz_BO      |0         |
      | quz_EC      |0         |
      | quz_PE      |0         |
      | rm_CH       |0         |
      | ro_RO       |0         |
      | ru_RU       |0         |
      | rw_RW       |0         |
      | sah_RU      |0         |
      | sa_IN       |0         |
      | se_FI       |0         |
      | se_NO       |0         |
      | se_SE       |0         |
      | si_LK       |0         |
      | sk_SK       |0         |
      | sl_SI       |0         |
      | sma_NO      |0         |
      | sma_SE      |0         |
      | smj_NO      |0         |
      | smj_SE      |0         |
      | smn_FI      |0         |
      | sms_FI      |0         |
      | sq_AL       |0         |
      | sr_Cyrl_BA  |0         |
      | sr_Cyrl_CS  |0         |
      | sr_Cyrl_ME  |0         |
      | sr_Cyrl_RS  |0         |
      | sr_Latn_BA  |0         |
      | sr_Latn_CS  |0         |
      | sr_Latn_ME  |0         |
      | sr_Latn_RS  |0         |
      | sv_FI       |0         |
      | sv_SE       |0         |
      | sw_KE       |0         |
      | syr_SY      |0         |
      | ta_IN       |0         |
      | te_IN       |0         |
      | tg_Cyrl_TJ  |0         |
      | th_TH       |0         |
      | tk_TM       |0         |
      | tn_ZA       |0         |
      | tr_TR       |0         |
      | tt_RU       |0         |
      | tzm_Latn_DZ |0         |
      | ug_CN       |0         |
      | uk_UA       |0         |
      | ur_PK       |0         |
      | uz_Cyrl_UZ  |0         |
      | uz_Latn_UZ  |0         |
      | vi_VN       |0         |
      | wo_SN       |0         |
      | xh_ZA       |0         |
      | yo_NG       |0         |
      | zh_CN       |0         |
      | zh_HK       |0         |
      | zh_MO       |0         |
      | zh_SG       |0         |
      | zh_TW       |0         |
      | zu_ZA       |0         |
