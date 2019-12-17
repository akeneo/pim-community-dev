import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

//TODO : to move in asset manager BC
export type Context = {
  locale: LocaleCode;
  channel: ChannelCode;
};
