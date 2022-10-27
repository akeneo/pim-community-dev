import styled from 'styled-components';
import {SkeletonPlaceholder} from 'akeneo-design-system';

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 20px;
`;

const Skeleton = styled(SkeletonPlaceholder)`
  width: 100%;
  height: 50px;
`;

const SkeletonContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
`;

const Styled = {
  FormContainer,
  Skeleton,
  SkeletonContainer,
};

export {Styled};
