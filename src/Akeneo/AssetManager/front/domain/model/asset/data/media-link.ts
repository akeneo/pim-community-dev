import ValueData from 'akeneoassetmanager/domain/model/asset/data';

class InvalidTypeError extends Error {}

export type NormalizedMediaLinkData = string | null;

class MediaLinkData extends ValueData {
  private constructor(private mediaLinkData: string) {
    super();

    if ('string' !== typeof mediaLinkData) {
      throw new InvalidTypeError('MediaLinkData expects a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(mediaLinkData: string): MediaLinkData {
    return new MediaLinkData(mediaLinkData);
  }

  public static createFromNormalized(mediaLinkData: NormalizedMediaLinkData): MediaLinkData {
    return new MediaLinkData(null === mediaLinkData ? '' : mediaLinkData);
  }

  public isEmpty(): boolean {
    return 0 === this.mediaLinkData.length || '<p></p>\n' === this.mediaLinkData;
  }

  public equals(data: ValueData): boolean {
    return data instanceof MediaLinkData && this.mediaLinkData === data.mediaLinkData;
  }

  public stringValue(): string {
    return this.mediaLinkData;
  }

  public normalize(): string {
    return this.mediaLinkData;
  }
}

export default MediaLinkData;
export const create = MediaLinkData.create;
export const denormalize = MediaLinkData.createFromNormalized;
