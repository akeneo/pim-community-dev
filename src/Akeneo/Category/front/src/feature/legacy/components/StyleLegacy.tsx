import {Field, Helper} from 'akeneo-design-system';
import styled from 'styled-components';

export const FormContainerLegacy = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

export const ErrorMessageLegacy = styled(Helper)`
  margin: 20px 0 0 0;
`;

export const PermissionFieldLegacy = styled(Field)`
  max-width: 400px;
`;
