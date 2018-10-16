import Channel, {denormalizeChannel} from 'akeneoreferenceentity/domain/model/channel';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';

export const hydrator = (denormalizeChannel: (normalizedChannel: any) => Channel) => (
  normalizedChannel: any
): Channel => {
  const expectedKeys = ['code', 'labels', 'locales'];

  validateKeys(normalizedChannel, expectedKeys, 'The provided raw channel seems to be malformed.');

  return denormalizeChannel(normalizedChannel);
};

export default hydrator(denormalizeChannel);
