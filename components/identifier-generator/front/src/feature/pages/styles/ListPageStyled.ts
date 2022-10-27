import styled from 'styled-components';
import {getColor, SkeletonPlaceholder} from 'akeneo-design-system';

const NoIdentifierMessage = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
`;

const Title = styled.div`
  font-size: 28px;
  font-weight: 400;
  margin-top: 30px;
`;

const HelpCenterLink = styled.a`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.purple100};
  cursor: pointer;
  margin-top: 5px;
  text-decoration: underline;
`;

const Container = styled.div`
  margin: 40px 20px;
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

const Label = styled.label`
  font-style: italic;
  color: ${getColor('brand', 100)};
`;

const Styled = {
  NoIdentifierMessage,
  Title,
  HelpCenterLink,
  Container,
  Skeleton,
  SkeletonContainer,
  Label
};

export {Styled};
