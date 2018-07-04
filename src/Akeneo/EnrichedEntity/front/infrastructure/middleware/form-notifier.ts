import __ from 'akeneoenrichedentity/tools/translator';

const messenger = require('oro/messenger');

export default () => () => (next: any) => (action: any) => {
  if ('POST_SAVE' === action.type) {
    messenger.notify('success', __('pim_enrich.entity.fallback.flash.update.success'));

    return;
  }

  if ('FAIL_SAVE' === action.type) {
    if (action.response && 500 == action.response.status) {
      const message = action.response.responseJSON ? action.response.responseJSON : action.response;

      console.error('Errors:', message);
    }

    messenger.notify('error', __('pim_enrich.entity.fallback.flash.update.fail'));

    return;
  }

  return next(action);
};
