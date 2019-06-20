class InvalidTypeError extends Error {}

export type NormalizedChannelReference = string | null;

export default class ChannelReference {
  private constructor(private channelReference: string | null) {
    if (!('string' === typeof channelReference || null === channelReference)) {
      throw new InvalidTypeError('ChannelReference expects a string or null as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(channelReference: string | null): ChannelReference {
    return new ChannelReference(channelReference);
  }

  public equals(channelReference: ChannelReference): boolean {
    return this.stringValue() === channelReference.stringValue();
  }

  public isEmpty(): boolean {
    return null === this.channelReference;
  }

  public stringValue(): string {
    return null === this.channelReference ? '' : this.channelReference;
  }

  public normalize(): string | null {
    return this.channelReference;
  }
}

export const createChannelReference = ChannelReference.create;
export const denormalizeChannelReference = ChannelReference.create;
