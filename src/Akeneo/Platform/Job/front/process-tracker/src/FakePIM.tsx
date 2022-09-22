import React, {useState} from 'react';
import styled from 'styled-components';
import {CardIcon, CommonStyle, DownloadIcon, getColor, MainNavigationItem} from 'akeneo-design-system';
import {ProcessTrackerApp} from './feature/ProcessTrackerApp';
import {JobInstanceDetail} from './feature/pages/JobInstanceDetail';
import {LastOperationsWidget} from './feature/pages/LastOperationsWidget';

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;

  ${CommonStyle}
`;

const Menu = styled.div`
  display: flex;
  flex-direction: column;
  width: 80px;
  height: 100vh;
  border-right: 1px solid ${getColor('grey', 60)};
  color: ${getColor('brand', 100)};
`;

const Page = styled.div`
  flex: 1;
`;

const FakePIM = () => {
  const [currentPage, setCurrentPage] = useState<string>('process-tracker');

  return (
    <Container>
      <Menu>
        <MainNavigationItem
          onClick={() => setCurrentPage('dashboard')}
          icon={<CardIcon />}
          active={'dashboard' === currentPage}
        >
          Dashboard
        </MainNavigationItem>
        <MainNavigationItem
          onClick={() => setCurrentPage('process-tracker')}
          icon={<CardIcon />}
          active={'process-tracker' === currentPage}
        >
          Process tracker
        </MainNavigationItem>
        <MainNavigationItem
          onClick={() => setCurrentPage('job-instance')}
          icon={<DownloadIcon />}
          active={'job-instance' === currentPage}
        >
          Exports
        </MainNavigationItem>
      </Menu>
      <Page>
        {'dashboard' === currentPage && <LastOperationsWidget />}
        {'process-tracker' === currentPage && <ProcessTrackerApp />}
        {'job-instance' === currentPage && <JobInstanceDetail code="csv_product_export" type="export" />}
      </Page>
    </Container>
  );
};

export {FakePIM};
