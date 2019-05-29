import Product, {
  NormalizedProduct,
  denormalizeProduct,
  PRODUCT_TYPE,
} from 'akeneoreferenceentity/domain/model/product/product';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference, {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {NormalizedCompleteness} from 'akeneoreferenceentity/domain/model/product/completeness';

const getProductCompleteness = (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
    channel: ChannelReference;
  }
): NormalizedCompleteness => {
  const channelCompleteness = normalizedProduct.meta.completenesses.find((channelCompleteness: any) =>
    context.channel.equals(createChannelReference(channelCompleteness.channel))
  );
  const localeCompleteness = channelCompleteness.locales[context.locale.stringValue()].completeness;

  return undefined === channelCompleteness
    ? {complete: 0, required: 0}
    : {
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
  const completenesses = normalizedProduct.meta.variant_product_completenesses.completenesses;

  return {
    complete: completenesses[context.channel.stringValue()][context.locale.stringValue()],
    required: normalizedProduct.meta.variant_product_completenesses.total,
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
  validateKeys(normalizedProduct, expectedKeys, 'The provided raw attribute seems to be malformed.');

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

const hydrateAttribute = hydrator(denormalizeProduct);

export default hydrateAttribute;
