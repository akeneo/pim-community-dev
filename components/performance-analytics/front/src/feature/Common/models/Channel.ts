export type ChannelCode = string;

export type Channel = {
  code: ChannelCode;
  labels: {
    [locale: string]: string;
  };
};

export const getChannelLabel = (channel: Channel, localCode: string): string => {
  return channel.labels && channel.labels[localCode] ? channel.labels[localCode] : `[${channel.code}]`;
};
