import styled from 'styled-components';
import {Section} from '@akeneo-pim-community/shared';

const ContextSwitchers = styled.div`
  margin-top: 10px;
`;

const FormGroup = styled(Section)`
  max-width: 400px;
`;

const ScrollablePageContent = styled.div`
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  overflow: auto;
`;

export {ContextSwitchers, FormGroup, ScrollablePageContent};
