import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {SubNavigationPanel} from 'akeneo-design-system';
import {useStorageState, useTranslate} from '@akeneo-pim-community/shared';

const Content = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const Container = styled.div`
  & > div > div > div { //Crazy dirty. Should be fixed properly
        height: calc(100vh - 54px);

        & > div {
          height: 100%;
        }
      }
    }
  }
`;
type SubNavigationProps = {
  title: string;
  children: ReactNode;
};

const SubNavigation = ({title, children}: SubNavigationProps) => {
  const [isCollapsed, setCollapsed] = useStorageState<boolean>(false, 'syndication-subnavigation');
  const translate = useTranslate();

  return (
    <Container>
      <SubNavigationPanel
        isOpen={!isCollapsed}
        open={() => setCollapsed(false)}
        close={() => setCollapsed(true)}
        closeTitle={translate('pim_common.close')}
        openTitle={translate('pim_common.open')}
      >
        <Content>{children}</Content>
      </SubNavigationPanel>
    </Container>
  );
};

export {SubNavigation};
