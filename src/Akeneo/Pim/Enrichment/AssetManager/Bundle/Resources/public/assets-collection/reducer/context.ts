import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';

export type LocaleCode = string;
export type ChannelCode = string;
export type ContextState = {
  locale: LocaleCode;
  channel: ChannelCode;
};

export const contextReducer = (
  state: ContextState = {locale: '', channel: ''},
  action: LocaleUpdatedAction | ChannelUpdatedAction
) => {
  switch (action.type) {
    case 'LOCALE_UPDATED':
      state = {...state, locale: action.locale};
      break;
    case 'CHANNEL_UPDATED':
      state = {...state, channel: action.channel};
      break;
    default:
      break;
  }

  return state;
};

type LocaleUpdatedAction = Action<'LOCALE_UPDATED'> & {locale: LocaleCode};
export const localeUpdated = (locale: LocaleCode): LocaleUpdatedAction => {
  return {type: 'LOCALE_UPDATED', locale};
};

type ChannelUpdatedAction = Action<'CHANNEL_UPDATED'> & {channel: ChannelCode};
export const channelUpdated = (channel: ChannelCode): ChannelUpdatedAction => {
  return {type: 'CHANNEL_UPDATED', channel};
};

export const selectContext = (state: AssetCollectionState): ContextState => {
  return state.context;
};
