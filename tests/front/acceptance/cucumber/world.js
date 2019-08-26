const UserBuilder = require('../../common/builder/user');
const LocaleBuilder = require('../../common/builder/locale');
const puppeteer = require('puppeteer');
const extensions = require(`${process.cwd()}/web/js/extensions.json`);
const fs = require('fs');
const path = require('path');
const htmlTemplate = fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8');
const translations = fs.readFileSync(path.join(process.cwd(), './web/js/translation/en_US.js'), 'utf-8');
const userBuilder = new UserBuilder();
const localeBuilder = new LocaleBuilder();

module.exports = function (cucumber) {
  const { Before, After, Status } = cucumber;

  Before({ timeout: 10 * 1000 }, async function () {
    this.baseUrl = 'http://pim.com';
    this.browser = await puppeteer.launch({
      devtools: true,
      ignoreHTTPSErrors: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
      headless: true,
      slowMo: 0,
      pipe: true,
      defaultViewport: {
        width: 1920,
        height: 1080
      }
    });

    this.page = await this.browser.newPage();
    await this.page.setRequestInterception(true);

    this.consoleLogs = [];

    this.page.on('console', message => {
      if (['error', 'warning'].includes(message.type())) {
        this.consoleLogs.push(message.text());
      }
    });

    this.page.on('request', request => {
      if (request.url() === `${this.baseUrl}/`) {
        return request.respond({
          contentType: 'text/html; charset=UTF-8',
          body: htmlTemplate,
        });
      }
      if (request.url().includes('/rest/user/')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(userBuilder.build())}`,
        });
      }

      if (request.url().includes('/js/extensions.json')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(extensions)}`,
        });
      }

      if (request.url().includes('/js/translation')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(translations)}`,
        });
      }

      if (request.url().includes('/security')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify({
            "oro_config_system":true,
            "pim_api_overall_access":true,
            "pim_api_attribute_list":true,
            "pim_api_attribute_edit":true,
            "pim_api_attribute_option_list":true,
            "pim_api_attribute_option_edit":true,
            "pim_api_attribute_group_list":true,
            "pim_api_attribute_group_edit":true,
            "pim_api_category_list":true,
            "pim_api_category_edit":true,
            "pim_api_channel_list":true,
            "pim_api_channel_edit":true,
            "pim_api_locale_list":true,
            "pim_api_family_list":true,
            "pim_api_family_edit":true,
            "pim_api_family_variant_list":true,
            "pim_api_family_variant_edit":true,
            "pim_api_currency_list":true,
            "pim_api_association_type_list":true,
            "pim_api_association_type_edit":true,
            "pim_user_user_index":true,
            "pim_user_user_create":true,
            "pim_user_user_edit":true,
            "pim_user_user_remove":true,
            "pim_user_group_index":true,
            "pim_user_group_create":true,
            "pim_user_group_edit":true,
            "pim_user_group_remove":true,
            "pim_user_role_index":true,
            "pim_user_role_create":true,
            "pim_user_role_edit":true,
            "pim_user_role_remove":true,
            "pim_enrich_api_connection_manage":true,
            "pim_enrich_locale_index":true,
            "pim_enrich_currency_index":true,
            "pim_enrich_currency_toggle":true,
            "pim_enrich_channel_index":true,
            "pim_enrich_channel_create":true,
            "pim_enrich_channel_edit":true,
            "pim_enrich_channel_remove":true,
            "pim_enrich_channel_history":true,
            "pim_pdf_generator_product_download":true,
            "pim_enrich_product_index":true,
            "pim_enrich_product_create":true,
            "pim_enrich_product_edit_attributes":true,
            "pim_enrich_product_remove":true,
            "pim_enrich_product_add_attribute":true,
            "pim_enrich_product_remove_attribute":true,
            "pim_enrich_product_categories_view":true,
            "pim_enrich_associations_view":true,
            "pim_enrich_associations_edit":true,
            "pim_enrich_associations_remove":true,
            "pim_enrich_product_change_family":true,
            "pim_enrich_product_change_state":true,
            "pim_enrich_product_add_to_groups":true,
            "pim_enrich_product_comment":true,
            "pim_enrich_product_history":true,
            "pim_enrich_product_model_create":true,
            "pim_enrich_product_model_edit_attributes":true,
            "pim_enrich_product_model_categories_view":true,
            "pim_enrich_product_model_history":true,
            "pim_enrich_product_model_remove":true,
            "pim_enrich_mass_edit":true,
            "pim_enrich_product_category_list":true,
            "pim_enrich_product_category_create":true,
            "pim_enrich_product_category_edit":true,
            "pim_enrich_product_category_remove":true,
            "pim_enrich_product_category_history":true,
            "pim_enrich_group_index":true,
            "pim_enrich_group_create":true,
            "pim_enrich_group_edit":true,
            "pim_enrich_group_remove":true,
            "pim_enrich_group_history":true,
            "pim_enrich_associationtype_index":true,
            "pim_enrich_associationtype_create":true,
            "pim_enrich_associationtype_edit":true,
            "pim_enrich_associationtype_remove":true,
            "pim_enrich_associationtype_history":true,
            "pim_enrich_family_index":true,
            "pim_enrich_family_create":true,
            "pim_enrich_family_edit_properties":true,
            "pim_enrich_family_edit_attributes":true,
            "pim_enrich_family_edit_variants":true,
            "pim_enrich_family_remove":true,
            "pim_enrich_family_history":true,
            "pim_enrich_family_variant_remove":true,
            "pim_enrich_attributegroup_index":true,
            "pim_enrich_attributegroup_create":true,
            "pim_enrich_attributegroup_edit":true,
            "pim_enrich_attributegroup_remove":true,
            "pim_enrich_attributegroup_sort":true,
            "pim_enrich_attributegroup_add_attribute":true,
            "pim_enrich_attributegroup_remove_attribute":true,
            "pim_enrich_attributegroup_history":true,
            "pim_enrich_attribute_index":true,
            "pim_enrich_attribute_create":true,
            "pim_enrich_attribute_edit":true,
            "pim_enrich_attribute_remove":true,
            "pim_enrich_attribute_sort":true,
            "pim_enrich_attribute_history":true,
            "pim_enrich_grouptype_index":true,
            "pim_enrich_grouptype_create":true,
            "pim_enrich_grouptype_edit":true,
            "pim_enrich_grouptype_remove":true,
            "pim_analytics_system_info_index":true,
            "pim_importexport_export_profile_index":true,
            "pim_importexport_export_profile_create":true,
            "pim_importexport_export_profile_show":true,
            "pim_importexport_export_profile_edit":true,
            "pim_importexport_export_profile_remove":true,
            "pim_importexport_export_profile_launch":true,
            "pim_importexport_export_profile_property_show":true,
            "pim_importexport_export_profile_property_edit":true,
            "pim_importexport_export_profile_history":true,
            "pim_importexport_export_profile_content_show":true,
            "pim_importexport_export_profile_content_edit":true,
            "pim_importexport_import_profile_index":true,
            "pim_importexport_import_profile_create":true,
            "pim_importexport_import_profile_show":true,
            "pim_importexport_import_profile_edit":true,
            "pim_importexport_import_profile_remove":true,
            "pim_importexport_import_profile_launch":true,
            "pim_importexport_import_profile_history":true,
            "pim_importexport_export_execution_index":true,
            "pim_importexport_export_execution_show":true,
            "pim_importexport_export_execution_download_log":true,
            "pim_importexport_export_execution_download_files":true,
            "pim_importexport_import_execution_index":true,
            "pim_importexport_import_execution_show":true,
            "pim_importexport_import_execution_download_log":true,
            "pim_importexport_import_execution_download_files":true,
            "pim_enrich_job_tracker_index":true
         })}`
        })
      }

      if (request.url().includes('/configuration/locale')) {
        const en_US = localeBuilder.withCode('en_US').build();

        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify([en_US])}`
        })
      }

      // @TODO - make a builder
      if (request.url().includes('localization/format/date')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify({
            "date": {
              "format": "MM\/dd\/yyyy",
              "defaultFormat": "yyyy-MM-dd"
            },
            "time": {
              "format": "MM\/dd\/yyyy hh:mm a",
              "defaultFormat": "yyyy-MM-dd HH:mm"
            },
            "timezone": "UTC",
            "language": "en_US",
            "12_hour_format": true
          }
          )}`
        })
      }
    });

    await this.page.goto(this.baseUrl);
    await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
    await this.page.evaluate(async () => await require('pim/init')());
    await this.page.evaluate(async () => await require('pim/user-context').initialize());
    await this.page.evaluate(async () => await require('pim/date-context').initialize());
    await this.page.evaluate(async () => await require('pim/init-translator').fetch());
    await this.page.evaluate(async () => await require('oro/init-layout')());
  });

  After(async function (scenario) {
    this.consoleLogs = this.consoleLogs || [];
    if (Status.FAILED === scenario.result.status) {
      if (0 < this.consoleLogs.length) {
        const logMessages = this.consoleLogs.reduce((result, message) => `${result}\nError logged: ${message}`, '');

        await this.attach(logMessages, 'text/plain');
        console.log(logMessages);
      }
    }

    // if (!this.parameters.debug) {
    //   await this.page.close();
    //   await this.browser.close();
    // }
  });
};
