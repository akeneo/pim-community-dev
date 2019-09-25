import {isString, isNull} from 'akeneoassetmanager/domain/model/utils';

type ChannelReference = string | null;
export default ChannelReference;

export const channelReferenceIsEmpty = (channelReference: ChannelReference): channelReference is null =>
  isNull(channelReference);
export const channelReferenceAreEqual = (first: ChannelReference, second: ChannelReference): boolean =>
  first === second;
export const channelReferenceStringValue = (channelReference: ChannelReference) =>
  isNull(channelReference) ? '' : channelReference;

export const denormalizeChannelReference = (channelReference: any): ChannelReference => {
  if (!(isString(channelReference) || isNull(channelReference))) {
    throw new Error('A channel reference should be a string or null');
  }

  return channelReference;
};
