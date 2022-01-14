import React, {useState, useEffect} from 'react';
import {Collapse, Helper, Pill} from 'akeneo-design-system';
import {Section, useTranslate, ValidationError, getErrorsForPath} from '@akeneo-pim-community/shared';
import {useChannels, useAssetFamily} from '../../../hooks';
import {
  AssetCollectionSelection,
  isDefaultAssetCollectionSelection,
  getDefaultAssetCollectionSelection,
  AssetCollectionMainMediaUrlSelection,
  getDefaultAssetCollectionMediaUrlSelection,
} from './model';
import {getAttributeAsMainMedia} from '../../..';
import {AssetCollectionMainMediaUrlSelector} from './AssetCollectionMainMediaUrlSelector';

type AssetCollectionUrlSelectorProps = {
  assetFamilyCode: string;
  selection: AssetCollectionMainMediaUrlSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: AssetCollectionSelection) => void;
};

const AssetCollectionUrlSelector = ({
  assetFamilyCode,
  selection,
  validationErrors,
  onSelectionChange,
}: AssetCollectionUrlSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const globalErrors = getErrorsForPath(validationErrors, '');
  const assetFamily = useAssetFamily(assetFamilyCode);

  useEffect(() => {
    if (null !== assetFamily && 0 !== channels.length) {
      const attribute = getAttributeAsMainMedia(assetFamily);
      const invalidChannel =
        (attribute.value_per_channel && null === selection.channel) ||
        (false === attribute.value_per_channel && null !== selection.channel);
      const invalidLocale =
        (attribute.value_per_locale && null === selection.locale) ||
        (false === attribute.value_per_locale && null !== selection.locale);

      if (invalidChannel || invalidLocale)
        onSelectionChange(getDefaultAssetCollectionMediaUrlSelection(assetFamily, channels));
    }
  }, [assetFamily, channels, selection, onSelectionChange]);

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultAssetCollectionSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        {globalErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        <AssetCollectionMainMediaUrlSelector
          selection={selection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      </Section>
    </Collapse>
  );
};

export {AssetCollectionUrlSelector, getDefaultAssetCollectionSelection};
export type {AssetCollectionSelection};
