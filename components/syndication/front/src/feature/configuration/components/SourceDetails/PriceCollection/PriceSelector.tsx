import React, {useState} from 'react';
import {Collapse, Pill} from 'akeneo-design-system';
import {Section, filterErrors, useTranslate, ValidationError, ChannelReference} from '@akeneo-pim-community/shared';
import {PriceCollectionSelection, isDefaultPriceCollectionSelection} from './model';
import {CurrencySelector} from './CurrencySelector';

type PriceSelectorProps = {
  channelReference: ChannelReference;
  selection: PriceCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
};

const PriceSelector = ({channelReference, selection, validationErrors, onSelectionChange}: PriceSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const currencyErrors = filterErrors(validationErrors, '[currency]');

  if ('price' !== selection.type) {
    throw new Error('PriceSelector can only be used with price type');
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultPriceCollectionSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <CurrencySelector
          value={selection.currency ?? ''}
          onChange={updatedValue => onSelectionChange({...selection, currency: updatedValue})}
          channelReference={channelReference}
          validationErrors={currencyErrors}
        />
      </Section>
    </Collapse>
  );
};

export {PriceSelector};
