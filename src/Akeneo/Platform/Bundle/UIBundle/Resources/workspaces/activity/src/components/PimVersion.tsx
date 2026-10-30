import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {usePimVersion} from '../hooks';
import {useTranslate} from '@akeneo-pim-community/shared';

const PimVersion = () => {
  const translate = useTranslate();
  const pimVersion: {version: string} = usePimVersion();

  return (
    <Container>
      {translate('pim_dashboard.version')}: {pimVersion.version}
    </Container>
  );
};

const Container = styled.div`
  text-align: center;
  color: ${getColor('grey', 100)};
  margin-top: 40px;
`;

export {PimVersion};
