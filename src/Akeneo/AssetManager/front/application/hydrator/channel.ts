import Channel, {denormalizeChannel} from 'akeneoassetmanager/domain/model/channel';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

export const hydrator = (denormalizeChannel: (normalizedChannel: any) => Channel) => (
  normalizedChannel: any
): Channel => {
  const expectedKeys = ['code', 'labels', 'locales'];

  validateKeys(normalizedChannel, expectedKeys, 'The provided raw channel seems to be malformed.');

  return denormalizeChannel(normalizedChannel);
};

export default hydrator(denormalizeChannel);
