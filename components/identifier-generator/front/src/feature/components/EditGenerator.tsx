import React, {useCallback, useState} from 'react';
import {TabBar} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

enum Tabs {
  GENERAL,
  PRODUCT_SELECTION,
  STRUCTURE,
}

const Container = styled.div`
  padding: 40px 40px 20px;
`;

const EditGenerator: React.FC = () => {
  const [currentTab, setCurrentTab] = useState(Tabs.GENERAL);
  const createSetTab = useCallback((value: Tabs) => () => setCurrentTab(value), []);
  const translate = useTranslate();

  return (
    <Container>
      <TabBar moreButtonTitle="More">
        <TabBar.Tab isActive={currentTab === Tabs.GENERAL} onClick={createSetTab(Tabs.GENERAL)}>
          {translate('pim_identifier_generator.tabs.general')}
        </TabBar.Tab>
        <TabBar.Tab isActive={currentTab === Tabs.PRODUCT_SELECTION} onClick={createSetTab(Tabs.PRODUCT_SELECTION)}>
          {translate('pim_identifier_generator.tabs.product_selection')}
        </TabBar.Tab>
        <TabBar.Tab isActive={currentTab === Tabs.STRUCTURE} onClick={createSetTab(Tabs.STRUCTURE)}>
          {translate('pim_identifier_generator.tabs.identifier_structure')}
        </TabBar.Tab>
      </TabBar>
    </Container>
  );
};

export {EditGenerator};
