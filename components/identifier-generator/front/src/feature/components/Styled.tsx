import styled from 'styled-components';
import {SkeletonPlaceholder, SectionTitle} from 'akeneo-design-system';

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 20px;
`;

const FullPageCenteredContent = styled.div`
  display: flex;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  height: 100vh;
  & svg {
    width: 500px;
  }
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

const StructureSectionTitle = styled(SectionTitle)`
  justify-content: space-between;
  margin-top: 20px;
  padding-bottom: 10px;
`;

const Styled = {
  FormContainer,
  Skeleton,
  SkeletonContainer,
  FullPageCenteredContent,
  StructureSectionTitle,
};

export {Styled};
