import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {updateColumns} from 'akeneoassetmanager/application/event/search';
import {notifyAttributeListUpdateFailed} from 'akeneoassetmanager/application/action/attribute/notify';
import ChannelReference, {
  channelReferenceIsEmpty,
  denormalizeChannelReference,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  denormalizeLocaleReference,
} from 'akeneoassetmanager/domain/model/locale-reference';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {generateKey} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeTypes, AttributeType} from 'akeneoassetmanager/application/configuration/attribute';
import {hasDataCellView} from 'akeneoassetmanager/application/configuration/value';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import denormalizeAttributes from 'akeneoassetmanager/application/denormalizer/attribute/attribute';

export class InvalidArgument extends Error {}

export const updateAttributeList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = getState().form.data;
  try {
    const attributes = await attributeFetcher.fetchAll(assetFamily.identifier);
    dispatch(attributeListGotUpdated(attributes));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};

export const attributeListGotUpdated = (attributes: Attribute[]) => (dispatch: any): void => {
  dispatch(attributeListUpdated(attributes));
  dispatch(updateAttributesColumns());
};

export const updateAttributesColumns = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const attributes = getState().attributes.attributes;
  if (null === attributes) return;
  const assetFamily = getState().form.data;
  const columnsToExclude = [assetFamily.attributeAsMainMedia, assetFamily.attributeAsLabel];
  dispatch(
    updateColumns(getColumns(attributes.map(denormalizeAttributes), getState().structure.channels, columnsToExclude))
  );
};

const getColumn = (attribute: Attribute, channel: ChannelReference, locale: LocaleReference): Column => {
  if (channelReferenceIsEmpty(channel)) {
    throw new InvalidArgument('A column cannot be generated from an empty ChannelReference');
  }

  if (localeReferenceIsEmpty(locale)) {
    throw new InvalidArgument('A column cannot be generated from an empty LocaleReference');
  }

  return {
    key: generateKey(
      attribute.identifier,
      attribute.valuePerChannel ? channel : denormalizeChannelReference(null),
      attribute.valuePerLocale ? locale : denormalizeLocaleReference(null)
    ),
    labels: attribute.getLabelCollection(),
    type: attribute.getType(),
    channel,
    locale: locale.normalize() as string,
    code: attribute.getCode(),
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
      return !columnsToExclude.map(column => column).includes(attribute.getIdentifier());
    })
    .sort((first: Attribute, second: Attribute) => first.order - second.order)
    .reduce((columns: Column[], attribute: Attribute) => {
      channels.forEach((channel: Channel) => {
        channel.locales.forEach((locale: Locale) => {
          columns.push(
            getColumn(attribute, denormalizeChannelReference(channel.code), denormalizeLocaleReference(locale.code))
          );
        });
      });

      return columns;
    }, []);
};
