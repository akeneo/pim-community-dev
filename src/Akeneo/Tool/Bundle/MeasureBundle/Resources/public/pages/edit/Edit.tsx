import React, {useState, useContext} from 'react';
import {useParams} from 'react-router-dom';
import {useMeasurementFamily} from 'akeneomeasure/hooks/use-measurement-families';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import styled from 'styled-components';
import {UnitTab} from 'akeneomeasure/pages/edit/UnitTab';
import {PropertyTab} from 'akeneomeasure/pages/edit/PropertyTab';

enum Tab {
  Units = 'units',
  Properties = 'properties',
}

const Container = styled.div``;
const TabContainer = styled.div``;
const Content = styled.div``;
const TabSelector = styled.div<{isActive: boolean}>``;

const Edit = () => {
  const {measurementFamilyCode} = useParams() as {measurementFamilyCode: string};
  const __ = useContext(TranslateContext);
  const [currentTab, setCurrentTab] = useState<Tab>(Tab.Units);
  const [measurementFamily, setMeasurmentFamily] = useMeasurementFamily(measurementFamilyCode);

  if (undefined === measurementFamilyCode || null === measurementFamily) {
    return null;
  }

  return (
    <Container>
      <TabContainer>
        {Object.values(Tab).map((tab: Tab) => (
          <TabSelector key={tab} onClick={() => setCurrentTab(tab)} isActive={currentTab === tab}>
            {__(`measurements.family.tab.${tab}`)}
          </TabSelector>
        ))}
      </TabContainer>
      <Content>
        {currentTab === Tab.Units && (
          <UnitTab measurementFamily={measurementFamily} onMeasurementFamilyChange={setMeasurmentFamily} />
        )}
        {currentTab === Tab.Properties && (
          <PropertyTab measurementFamily={measurementFamily} onMeasurementFamilyChange={setMeasurmentFamily} />
        )}
      </Content>
    </Container>
  );
};

export {Edit};
