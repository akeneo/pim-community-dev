import {Field, Helper} from 'akeneo-design-system';
import styled from 'styled-components';

export const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

export const ErrorMessage = styled(Helper)`
  margin: 20px 0 0 0;
`;

export const PermissionField = styled(Field)`
  max-width: 400px;
`;
