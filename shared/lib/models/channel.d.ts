import { Locale, LocaleCode } from '../models';
import { LabelCollection } from '../models';
declare type ChannelCode = string;
declare type Channel = {
    code: ChannelCode;
    labels: LabelCollection;
    locales: Locale[];
};
declare const getChannelLabel: (channel: Channel, locale: LocaleCode) => string;
declare const denormalizeChannel: (channel: any) => Channel;
export { getChannelLabel, denormalizeChannel };
export type { ChannelCode, Channel };
