import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

export type Context = {
  locale: LocaleCode;
  channel: ChannelCode;
};
