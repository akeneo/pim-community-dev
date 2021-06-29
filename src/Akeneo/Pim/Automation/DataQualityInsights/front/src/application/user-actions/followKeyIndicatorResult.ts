import {KeyIndicatorExtraData} from '../../domain';

export type FollowKeyIndicatorResultHandler = (
  channelCode: string,
  localeCode: string,
  familyCode: string | null,
  categoryId: string | null,
  rootCategoryId: string | null,
  extraData: KeyIndicatorExtraData | undefined
) => void;
