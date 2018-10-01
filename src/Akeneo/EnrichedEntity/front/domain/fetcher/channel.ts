import Channel from 'akeneoreferenceentity/domain/model/channel';

export default interface Fetcher {
  fetchAll: () => Promise<Channel[]>;
}
