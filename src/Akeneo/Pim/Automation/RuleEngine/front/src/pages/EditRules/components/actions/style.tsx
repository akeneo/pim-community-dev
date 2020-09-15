import styled from 'styled-components';

export const ActionFormContainer = styled.div`
  max-width: 300px;
`;

export const LargeActionGrid = styled.div`
  margin-top: 18px;
  display: grid;
  grid-template-columns: 1fr 300px;
  grid-gap: 40px;
`;

export const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

export const ErrorBlock = styled.div`
  margin-top: 5px;
`;

export const EmptySourceHelper = styled.div`
  background-image: url('/bundles/pimui/images/icon-info.svg');
  background-repeat: no-repeat;
  padding-left: 28px;
  height: 21px;
  line-height: 21px;
`;
