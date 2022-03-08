import {KeyIndicatorExtraData} from '../../domain';
import {ProductType} from '../../domain/Product.interface';

export type FollowKeyIndicatorResultHandler = (
  channelCode: string,
  localeCode: string,
  productType: ProductType,
  familyCode?: string | null,
  categoryId?: string | null,
  rootCategoryId?: string | null,
  extraData?: KeyIndicatorExtraData
) => void;
