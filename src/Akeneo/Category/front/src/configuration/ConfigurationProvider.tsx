import React, {createContext, FC, useCallback, useState} from 'react';

type Configuration = {
  features: {
    permission: boolean;
    enrich_category: boolean;
  };
  acls: {
    pim_enrich_product_categories_view: boolean;
    pim_enrich_product_category_create: boolean;
    pim_enrich_product_category_edit: boolean;
    pim_enrich_product_category_history: boolean;
    pim_enrich_product_category_list: boolean;
    pim_enrich_product_category_remove: boolean;
    pimee_enrich_category_edit_permissions: boolean;
    pimee_enrich_category_edit_template: boolean;
  };
};

type WriteConfiguration = {
  features?: {
    permission?: boolean;
    enrich_category?: boolean;
  };
  acls?: {
    pim_enrich_product_categories_view?: boolean;
    pim_enrich_product_category_create?: boolean;
    pim_enrich_product_category_edit?: boolean;
    pim_enrich_product_category_history?: boolean;
    pim_enrich_product_category_list?: boolean;
    pim_enrich_product_category_remove?: boolean;
    pimee_enrich_category_edit_permissions?: boolean;
    pimee_enrich_category_edit_template?: boolean;
  };
}

type ConfigurationState = {
  configuration: Configuration,
  setDefaultCommunitySettings: () => void;
  setDefaultGrowthSettings: () => void;
  setDefaultEnterpriseSettings: () => void;
  updateConfiguration: (config: WriteConfiguration) => void;
}

const ConfigurationContext = createContext<ConfigurationState | undefined>(undefined);

const ConfigurationProvider: FC = ({children}) => {
  const [configuration, setConfiguration] = useState({
    features: {
      permission: true,
      enrich_category: true,
    },
    acls: {
      pim_enrich_product_categories_view: true,
      pim_enrich_product_category_create: true,
      pim_enrich_product_category_edit: true,
      pim_enrich_product_category_history: true,
      pim_enrich_product_category_list: true,
      pim_enrich_product_category_remove: true,
      pimee_enrich_category_edit_permissions: true,
      pimee_enrich_category_edit_template: true,
    }
  });

  const setDefaultCommunitySettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: false,
        enrich_category: false,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: false,
        pimee_enrich_category_edit_template: false,
      }
    });
  }, []);

  const setDefaultGrowthSettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: false,
        enrich_category: true,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: false,
        pimee_enrich_category_edit_template: true,
      }
    });
  }, []);

  const setDefaultEnterpriseSettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: true,
        enrich_category: true,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: true,
        pimee_enrich_category_edit_template: true,
      }
    });
  }, []);

  // @todo split updateConfiguration in setFeature and setAcl
  const updateConfiguration = useCallback((config: WriteConfiguration) => {
    setConfiguration((configuration) => {
      console.log(`setConfiguration ${JSON.stringify(configuration)} |  ${JSON.stringify(config)}`)
      return {
        features: {
          ...configuration.features,
          ...(config.features ?? {}),
        },
        acls: {
          ...configuration.acls,
          ...(config.acls ?? {}),
        }
      }
    })
  }, []);

  const state = {
    configuration,
    updateConfiguration,
    setDefaultCommunitySettings,
    setDefaultGrowthSettings,
    setDefaultEnterpriseSettings,
  };

  return <ConfigurationContext.Provider value={state}>{children}</ConfigurationContext.Provider>
}
export type {ConfigurationState, Configuration, WriteConfiguration};
export {ConfigurationContext, ConfigurationProvider};
