import __ from 'akeneoenrichedentity/tools/translator';
import {toggleSidebar, setUpTabs, updateCurrentTab} from 'akeneoenrichedentity/application/event/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

export const setUpSidebar = () => async (dispatch: any): Promise<void> => {
  dispatch(toggleSidebar(false));

  const tabs: Tab[] = [
    {
      code: 'pim-enriched-entity-edit-form-records',
      label: __('pim_enriched_entity.enriched_entity.records_tab'),
    },
    {
      code: 'pim-enriched-entity-edit-form-attributes',
      label: __('pim_enriched_entity.enriched_entity.attributes_tab'),
    },
    {
      code: 'pim-enriched-entity-edit-form-properties',
      label: __('pim_enriched_entity.enriched_entity.properties_tab'),
    },
  ];
  dispatch(setUpTabs(tabs));
  dispatch(updateCurrentTab(tabs[2].code));
};
