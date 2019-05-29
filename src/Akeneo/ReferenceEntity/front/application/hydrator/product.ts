import Product, {
  NormalizedProduct,
  denormalizeProduct,
  PRODUCT_TYPE,
} from 'akeneoreferenceentity/domain/model/product/product';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference, {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';

export const hydrator = (denormalize: (denormalizeProduct: NormalizedProduct) => Product) => (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
    channel: ChannelReference;
  }
): Product => {
  const expectedKeys = ['meta'];
  validateKeys(normalizedProduct, expectedKeys, 'The provided raw attribute seems to be malformed.');

  const channelCompleteness = normalizedProduct.meta.completeness.find((channelCompleteness: any) =>
    context.channel.equals(createChannelReference(channelCompleteness.channel))
  );
  const completeness =
    undefined === channelCompleteness
      ? {complete: 0, required: 0}
      : {
          complete: channelCompleteness.locales[context.locale.stringValue()].completeness.complete,
          required: channelCompleteness.locales[context.locale.stringValue()].completeness.required,
        };

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
