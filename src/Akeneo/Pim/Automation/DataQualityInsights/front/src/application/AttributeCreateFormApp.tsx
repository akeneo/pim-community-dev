import React, {FC} from 'react';
import {Provider} from 'react-redux';
import {attributeEditFormStore} from '../infrastructure/store';
import {Attribute} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {AttributeEditFormContextProvider} from './context/AttributeEditFormContext';
import {ATTRIBUTE_CREATE_FORM_LABELS_TAB} from './constant';
import TabContent, {useTabState} from './component/Common/Tab/TabContent';
import SpellcheckLabelsList from './component/AttributeEditForm/TabContent/SpellcheckLabelsList';

interface AttributeCreateFormAppProps {
  attribute: Attribute;
  renderingId: number;
}

const AttributeCreateFormApp: FC<AttributeCreateFormAppProps> = ({attribute, renderingId}) => {
  const tabState = useTabState();

  return (
    <>
      <AttributeEditFormContextProvider attribute={attribute} renderingId={renderingId}>
        <Provider store={attributeEditFormStore}>
          <TabContent tabId={ATTRIBUTE_CREATE_FORM_LABELS_TAB} {...tabState}>
            <SpellcheckLabelsList />
          </TabContent>
        </Provider>
      </AttributeEditFormContextProvider>
    </>
  );
};

export default AttributeCreateFormApp;
