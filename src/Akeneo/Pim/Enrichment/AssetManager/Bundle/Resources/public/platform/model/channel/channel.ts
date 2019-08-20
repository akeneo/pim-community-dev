import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';

export type ChannelCode = string;
export type Channel = {
  code: ChannelCode;
  labels: Labels;
  locales: Locale[];
};

export type ChannelReference = ChannelCode | null;
