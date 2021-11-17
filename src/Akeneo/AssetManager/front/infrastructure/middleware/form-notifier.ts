import {Notify, Translate} from '@akeneo-pim-community/shared';

export default (notify: Notify, translate: Translate) => () => (next: any) => (action: any) => {
  if ('NOTIFY' === action.type) {
    notify(action.level, translate(action.message, action.parameters));

    return;
  }

  return next(action);
};
