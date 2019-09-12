import {ChannelCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';

export type Context = {
  locale: LocaleCode;
  channel: ChannelCode;
};
