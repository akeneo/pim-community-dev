import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

export const Separator = styled.div`
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};
  margin: 0 10px;
<<<<<<< HEAD
  height: 20px;
=======
>>>>>>> 34d4702ae1 (RAC-486: move mass edit launcher into a dedicated component for unit test)

  &:first-child,
  &:last-child {
    margin: 0;
  }
`;

export default Separator;
