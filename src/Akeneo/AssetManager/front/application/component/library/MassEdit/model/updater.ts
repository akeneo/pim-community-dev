import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import Data from 'akeneoassetmanager/domain/model/asset/data';

type Updater = {
  id: string;
  channel: ChannelReference;
  locale: LocaleReference;
  attribute: NormalizedAttribute;
  data: Data;
  action: 'replace' | 'append';
};

const normalizeUpdater = (updater: Updater) => {
  return {
    id: updater.id,
    channel: updater.channel,
    locale: updater.locale,
    attribute: updater.attribute.identifier,
    data: updater.data,
    action: updater.action,
  };
};

const normalizeUpdaterCollection = (updaterCollection: Updater[]) => {
  return updaterCollection.map(updater => normalizeUpdater(updater));
};

export {normalizeUpdaterCollection, Updater};
