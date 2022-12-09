import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

const AttributeSelectorContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const SubFields = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const SubField = styled.div`
  display: flex;
  gap: 5px;
  align-items: baseline;
  margin-left: 8px;
  color: ${getColor('grey', 100)};
`;

const InnerField = styled.div`
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: 5px;
`;

export {AttributeSelectorContainer, SubFields, SubField, InnerField};
