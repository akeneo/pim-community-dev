import {FollowLocaleHandler, Locale} from '@akeneo-pim-community/settings-ui';

const Router = require('pim/router');

const followEditLocale: FollowLocaleHandler = (locale: Locale) => {
  Router.redirectToRoute('pimee_enrich_locale_edit', {id: locale.id});
};

export {followEditLocale};
