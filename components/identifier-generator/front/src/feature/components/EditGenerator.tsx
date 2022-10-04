import React, {useCallback, useState} from 'react';
import {IdentifierGenerator} from '../../models';
import {TabBar} from 'akeneo-design-system';
import styled from 'styled-components';

type EditGeneratorProps = {
  generator: IdentifierGenerator | null;
};

enum Tabs {
  GENERAL,
  PRODUCT_SELECTION,
  STRUCTURE,
  HISTORY,
}

const Container = styled.div`
  padding: 40px 40px 20px;
`;

const EditGenerator: React.FC<EditGeneratorProps> = ({generator}) => {
  const [currentTab, setCurrentTab] = useState(Tabs.GENERAL);
  const createSetTab = useCallback((value: Tabs) => () => setCurrentTab(value), []);

  return (
    <Container>
      <TabBar moreButtonTitle="More">
        <TabBar.Tab isActive={currentTab === Tabs.GENERAL} onClick={createSetTab(Tabs.GENERAL)}>
          General
        </TabBar.Tab>
        <TabBar.Tab isActive={currentTab === Tabs.PRODUCT_SELECTION} onClick={createSetTab(Tabs.PRODUCT_SELECTION)}>
          Product Selection
        </TabBar.Tab>
        <TabBar.Tab isActive={currentTab === Tabs.STRUCTURE} onClick={createSetTab(Tabs.STRUCTURE)}>
          Identifier Structure
        </TabBar.Tab>
        <TabBar.Tab isActive={currentTab === Tabs.HISTORY} onClick={createSetTab(Tabs.HISTORY)}>
          Identifier Structure
        </TabBar.Tab>
      </TabBar>
    </Container>
  );
};

export {EditGenerator};
