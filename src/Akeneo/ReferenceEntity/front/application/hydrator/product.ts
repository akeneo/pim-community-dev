import Product, {
  NormalizedProduct,
  denormalizeProduct,
  PRODUCT_TYPE,
} from 'akeneoreferenceentity/domain/model/product/product';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference, {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {NormalizedCompleteness} from 'akeneoreferenceentity/domain/model/product/completeness';
import {accessProperty} from 'akeneoreferenceentity/tools/property';

const getProductCompleteness = (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
    channel: ChannelReference;
  }
): NormalizedCompleteness => {
  const completenesses = accessProperty(normalizedProduct, `meta.completenesses`, []);
  const channelCompleteness = completenesses.find((channelCompleteness: any) =>
    context.channel.equals(createChannelReference(channelCompleteness.channel))
  );

  if (undefined === channelCompleteness) {
    return {required: 0, complete: 0};
  }

  const localeCompleteness = accessProperty(
    channelCompleteness,
    `locales.${context.locale.stringValue()}.completeness`,
    {
      required: 0,
      missing: 0,
    }
  );

  return {
    complete: localeCompleteness.required - localeCompleteness.missing,
    required: localeCompleteness.required,
  };
};

const getProductModelCompleteness = (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
    channel: ChannelReference;
  }
): NormalizedCompleteness => {
  const complete = accessProperty(
    normalizedProduct,
    `meta.variant_product_completenesses.completenesses.${context.channel.stringValue()}.${context.locale.stringValue()}`,
    0
  );
  const required = accessProperty(normalizedProduct, 'meta.variant_product_completenesses.total', 0);

  return {
    complete,
    required,
  };
};

export const hydrator = (denormalize: (denormalizeProduct: NormalizedProduct) => Product) => (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
    channel: ChannelReference;
  }
): Product => {
  const expectedKeys = ['meta'];
  validateKeys(normalizedProduct, expectedKeys, 'The provided raw product seems to be malformed.');

  const completeness =
    PRODUCT_TYPE === normalizedProduct.meta.model_type
      ? getProductCompleteness(normalizedProduct, context)
      : getProductModelCompleteness(normalizedProduct, context);

  return denormalize({
    id: String(normalizedProduct.meta.id),
    identifier:
      PRODUCT_TYPE === normalizedProduct.meta.model_type ? normalizedProduct.identifier : normalizedProduct.code,
    type: normalizedProduct.meta.model_type,
    labels: normalizedProduct.meta.label,
    image: normalizedProduct.meta.image,
    completeness,
  });
};

const hydrateProduct = hydrator(denormalizeProduct);

export default hydrateProduct;
