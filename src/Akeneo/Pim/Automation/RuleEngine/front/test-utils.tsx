import React from 'react';
import {FormContext, useForm} from 'react-hook-form';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {ThemeProvider} from 'styled-components';
import {ApplicationDependenciesProvider} from './src/dependenciesTools';
import {pimTheme} from 'akeneo-design-system';
import {ConfigContext} from '../front/src/context/ConfigContext';
import renderText from '../front/src/pages/EditRules/components/actions/attribute/TextValue';
import renderTextArea from '../front/src/pages/EditRules/components/actions/attribute/TextAreaValue';
import renderDate from '../front/src/pages/EditRules/components/actions/attribute/DateValue';
import renderSimpleSelect from '../front/src/pages/EditRules/components/actions/attribute/SimpleSelectValue';
import renderMultiSelect from '../front/src/pages/EditRules/components/actions/attribute/MultiSelectValue';
import renderNumber from '../front/src/pages/EditRules/components/actions/attribute/NumberValue';
import renderBoolean from '../front/src/pages/EditRules/components/actions/attribute/BooleanValue';
import renderPriceCollection from '../front/src/pages/EditRules/components/actions/attribute/PriceCollectionValue';
import renderMeasurement from '../front/src/pages/EditRules/components/actions/attribute/MeasurementValue';
import renderAssetCollection from '../front/src/pages/EditRules/components/actions/attribute/AssetCollectionValue';
import renderMultiReferenceEntity from '../front/src/pages/EditRules/components/actions/attribute/MultiReferenceEntityValue';
import renderSimpleReferenceEntity from '../front/src/pages/EditRules/components/actions/attribute/SimpleReferenceEntityValue';

jest.mock('./src/dependenciesTools/provider/dependencies.ts');

type ToRegister = {
  name: string;
};

type ReactHookFormWrapperInitializer = {
  defaultValues?: any;
  toRegister?: ToRegister[];
};

const LegacyDependencies: React.FC = ({children}) => (
  <ApplicationDependenciesProvider>{children}</ApplicationDependenciesProvider>
);

const AkeneoThemeProvider: React.FC = ({children}) => <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>;

type Context = {
  all?: boolean;
  legacy?: boolean;
  theme?: boolean;
  reactHookForm?: boolean;
};

const getProviders = (context: Context) => {
  const {legacy, theme} = context;
  if (theme) {
    return AkeneoThemeProvider;
  }
  if (legacy) {
    return LegacyDependencies;
  }
  throw new Error("[TEST-UTILS]: The provider you asked doesn't exist");
};

export const renderWithProviders = (ui: React.ReactElement, context?: Context, options?: any) => {
  /*
    Providers with react-hook-form needs to be define here, to give them some specific props.
  */
  const ReactHookFormProvider: React.FC<ReactHookFormWrapperInitializer> = ({
    children,
    defaultValues = {},
    toRegister = [],
  }) => {
    const form = useForm({defaultValues});
    const {register} = form;
    React.useEffect(() => {
      toRegister?.forEach(register);
    }, [register]);
    return <FormContext {...form}>{children}</FormContext>;
  };

  const attributeValueConfig = {
    pim_catalog_text: {default: renderText },
    pim_catalog_textarea: {default: renderTextArea },
    pim_catalog_date: {default: renderDate },
    pim_catalog_simpleselect: {default: renderSimpleSelect },
    pim_catalog_multiselect: {default: renderMultiSelect },
    pim_catalog_number: {default: renderNumber },
    pim_catalog_boolean: {default: renderBoolean },
    pim_catalog_price_collection: {default: renderPriceCollection },
    pim_catalog_metric: {default: renderMeasurement },
    pim_catalog_asset_collection: {default: renderAssetCollection },
    akeneo_reference_entity_collection: {default: renderMultiReferenceEntity },
    akeneo_reference_entity: {default: renderSimpleReferenceEntity },
  }

  const AllProviders: React.FC = ({children}) => {
    return (
      <LegacyDependencies>
        <AkeneoThemeProvider>
          <ReactHookFormProvider defaultValues={options?.defaultValues} toRegister={options?.toRegister}>
            <ConfigContext.Provider value={{attributeValueConfig}}>
              {children}
            </ConfigContext.Provider>
          </ReactHookFormProvider>
        </AkeneoThemeProvider>
      </LegacyDependencies>
    );
  };
  if (context?.all) {
    return render(ui, {wrapper: AllProviders});
  } else if (context?.reactHookForm) {
    return render(ui, {wrapper: ReactHookFormProvider});
  } else if (context) {
    return render(ui, {wrapper: getProviders(context)});
  }
  return render(ui);
};

export * from '@testing-library/react';
export {renderWithProviders as render};
