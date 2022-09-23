import {AssetCollectionMainMediaNotFoundError} from '../../components/SourceDetails/error';

type Attribute = {
  identifier: string;
  type: string;
  value_per_locale: boolean;
  value_per_channel: boolean;
};

type AssetFamily = {
  identifier: string;
  attribute_as_main_media: string;
  attributes: Attribute[];
};

const getAttributeAsMainMedia = (assetFamily: AssetFamily): Attribute => {
  const attributeAsMainMedia = assetFamily.attributes.find(
    ({identifier}) => identifier === assetFamily.attribute_as_main_media
  );

  if (!attributeAsMainMedia) {
    throw new AssetCollectionMainMediaNotFoundError(
      `"${assetFamily.attribute_as_main_media}" attribute as main media does not exist in the family "${assetFamily.identifier}"`
    );
  }

  return attributeAsMainMedia;
};

export type {AssetFamily};
export {getAttributeAsMainMedia};
