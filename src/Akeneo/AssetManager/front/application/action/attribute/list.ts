import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {updateColumns} from 'akeneoassetmanager/application/event/search';
import {notifyAttributeListUpdateFailed} from 'akeneoassetmanager/application/action/attribute/notify';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {generateKey} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeTypes, AttributeType} from 'akeneoassetmanager/application/configuration/attribute';
import {hasDataCellView} from 'akeneoassetmanager/application/configuration/value';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

export class InvalidArgument extends Error {}

export const updateAttributeList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = denormalizeAssetFamily(getState().form.data);
  try {
    const attributes = await attributeFetcher.fetchAll(assetFamily.getIdentifier());
    dispatch(attributeListGotUpdated(attributes));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};

export const attributeListGotUpdated = (attributes: Attribute[]) => (
  dispatch: any,
  getState: () => EditState
): void => {
  dispatch(attributeListUpdated(attributes));

  const assetFamily = denormalizeAssetFamily(getState().form.data);
  const columnsToExclude = [assetFamily.getAttributeAsImage(), assetFamily.getAttributeAsLabel()];

  dispatch(updateColumns(getColumns(attributes, getState().structure.channels, columnsToExclude)));
};

const getColumn = (attribute: Attribute, channel: ChannelReference, locale: LocaleReference): Column => {
  if (channel.isEmpty()) {
    throw new InvalidArgument('A column cannot be generated from an empty ChannelReference');
  }

  if (locale.isEmpty()) {
    throw new InvalidArgument('A column cannot be generated from an empty LocaleReference');
  }

  return {
    key: generateKey(
      attribute.identifier,
      attribute.valuePerChannel ? channel : ChannelReference.create(null),
      attribute.valuePerLocale ? locale : LocaleReference.create(null)
    ),
    labels: attribute.getLabelCollection().normalize(),
    type: attribute.getType(),
    channel: channel.normalize() as string,
    locale: locale.normalize() as string,
    code: attribute.getCode().stringValue(),
    attribute: attribute.normalize(),
  };
};

export const getColumns = (attributes: Attribute[], channels: Channel[], columnsToExclude: AttributeIdentifier[]) => {
  const attributeTypes = getAttributeTypes()
    .filter((attributeType: AttributeType) => hasDataCellView(attributeType.identifier))
    .map((attributeType: AttributeType) => attributeType.identifier);
  return attributes
    .filter((attribute: Attribute) => attributeTypes.includes(attribute.getType()))
    .filter((attribute: Attribute) => {
      return !columnsToExclude.map(column => column.stringValue()).includes(attribute.getIdentifier().stringValue());
    })
    .sort((first: Attribute, second: Attribute) => first.order - second.order)
    .reduce((columns: Column[], attribute: Attribute) => {
      channels.forEach((channel: Channel) => {
        channel.locales.forEach((locale: Locale) => {
          columns.push(
            getColumn(attribute, ChannelReference.create(channel.code), LocaleReference.create(locale.code))
          );
        });
      });

      return columns;
    }, []);
};
