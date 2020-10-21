import React, {FC} from 'react';
import {Provider} from "react-redux";

import {Attribute} from "@akeneo-pim-community/data-quality-insights/src/domain";
import {ATTRIBUTE_EDIT_FORM_LABELS_TAB, ATTRIBUTE_EDIT_FORM_OPTIONS_TAB} from "./constant";
import {AttributeEditFormContextProvider} from "./context/AttributeEditFormContext";
import {attributeEditFormStore} from "../infrastructure/store";
import TabContent, {useTabState} from "./component/Common/Tab/TabContent";
import SpellcheckOptionsList from "./component/AttributeEditForm/TabContent/SpellcheckOptionsList";
import SpellcheckLabelsList from "./component/AttributeEditForm/TabContent/SpellcheckLabelsList";
import AddQualityBadgesOnOptionsList from "./component/AttributeEditForm/TabContent/AddQualityBadgesOnOptionsList";
import RefreshEvaluationWhenAttributeOptionsChanged
  from "./component/AttributeEditForm/TabContent/RefreshEvaluationWhenAttributeOptionsChanged";


interface AttributeEditFormAppProps {
  attribute: Attribute;
  renderingId: number;
}

const AttributeEditFormApp: FC<AttributeEditFormAppProps> = ({attribute, renderingId}) => {
  const tabState = useTabState();

  return (
    <>
        <AttributeEditFormContextProvider attribute={attribute} renderingId={renderingId}>
          <Provider store={attributeEditFormStore}>
            <TabContent tabId={ATTRIBUTE_EDIT_FORM_LABELS_TAB} {...tabState}>
              <SpellcheckLabelsList/>
            </TabContent>
            <TabContent tabId={ATTRIBUTE_EDIT_FORM_OPTIONS_TAB} {...tabState}>
              <AddQualityBadgesOnOptionsList />
              <RefreshEvaluationWhenAttributeOptionsChanged/>
              <SpellcheckOptionsList/>
            </TabContent>
          </Provider>
        </AttributeEditFormContextProvider>
    </>
  );
};

export default AttributeEditFormApp;
