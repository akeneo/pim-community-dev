import Channel from 'akeneoenrichedentity/domain/model/channel';

export default interface Fetcher {
  fetchAll: () => Promise<Channel[]>;
}
