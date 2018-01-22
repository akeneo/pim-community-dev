import Channel from 'pimfront/app/domain/model/channel'

export const channelsUpdated = (channels: Channel[]) => {
  return {type: 'CHANNELS_UPDATED', channels};
};
