import {KeyIndicatorExtraData} from '../../domain';
import {ProductType} from '../../domain/Product.interface';

export type ProductsKeyIndicatorLinkCallback = (
  channelCode: string,
  localeCode: string,
  entityType: ProductType,
  familyCode?: string | null,
  categoryId?: string | null,
  rootCategoryId?: string | null,
  extraData?: KeyIndicatorExtraData
) => void;

export type AttributesKeyIndicatorLinkCallback = (
  localeCode: string,
  familyCode?: string | null,
  categoryId?: string | null,
  extraData?: KeyIndicatorExtraData
) => void;
