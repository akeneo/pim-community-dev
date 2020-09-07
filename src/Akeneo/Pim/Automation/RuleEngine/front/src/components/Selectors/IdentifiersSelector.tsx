import React from 'react';
import {
  InitSelectionCallback,
  Select2Ajax,
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';
import { ProductModelCode, ProductIdentifier } from '../../models';

type Identifier = ProductIdentifier | ProductModelCode;

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  entityType?: 'product' | 'product_model';
  value: Identifier[];
  onChange?: (value: Identifier[]) => void;
  validation?: { required?: string; validate?: (value: any) => string | true };
  name: string;
  id: string;
};

const dataProvider = (term: string, page: number, type?: string) => {
  return {
    search: term,
    options: {
      limit: 20,
      page: page,
      type: type,
    },
  };
};

type Select2Results = {
  results: Select2Option[];
};

const handleResults = (json: Select2Results) => {
  return {
    more: 20 === json.results.length,
    ...json,
  };
};

const initSelectedIdentifiers = (
  selectedIdentifiers: Identifier[],
  callback: InitSelectionCallback
): void => {
  callback(
    selectedIdentifiers.map(identifier => {
      return {
        id: identifier,
        text: identifier,
      };
    })
  );
};

const IdentifiersSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  value,
  onChange,
  validation,
  name,
  id,
  entityType,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const handleChange = (value: Select2Value[]) => {
    if (onChange) {
      onChange(value as Identifier[]);
    }
  };

  const ajax = React.useMemo<Select2Ajax>(() => {
    return {
      url: router.generate(
        'pimee_enrich_rule_definition_search_products_and_product_models'
      ),
      quietMillis: 250,
      cache: true,
      data: (term: string, page: number) =>
        dataProvider(term, page, entityType),
      results: (json: Select2Results) => handleResults(json),
    };
  }, [router]);

  return (
    <Select2MultiAsyncWrapper
      {...remainingProps}
      name={name}
      label={label || translate('pim_common.identifier')}
      data-testid={id}
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      ajax={ajax}
      initSelection={(_element, callback) =>
        initSelectedIdentifiers(value, callback)
      }
      validation={validation}
    />
  );
};

export { Identifier, IdentifiersSelector };
