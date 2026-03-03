import * as $ from 'jquery';
import * as _ from 'underscore';
const __ = require('oro/translator');
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');
const containerTemplate = require('pim/templates/user/form/fields/select-profile-container');

type InterfaceNormalizedProfile = {
  code: string;
  label: string;
};

class SelectProfile extends BaseSelect {
  // @ts-ignore
  private containerTemplate = _.template(containerTemplate);

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),

      FetcherRegistry.getFetcher('user-profiles')
        .fetchAll()
        .then((profiles: InterfaceNormalizedProfile[]) => {
          this.config.choices = profiles;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  formatChoices(profiles: InterfaceNormalizedProfile[]): {[key: string]: string} {
    return profiles.reduce((result: {[key: string]: string}, profile: InterfaceNormalizedProfile) => {
      result[profile.code] = __(profile.label);

      return result;
    }, {});
  }

  /**
   * {@inheritdoc}
   */
  getFieldValue(field: HTMLInputElement) {
    return null === field.value ? '' : field.value;
  }
}

export = SelectProfile;
