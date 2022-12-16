import styled from 'styled-components';
import {AkeneoThemedProps, getColor, SkeletonPlaceholder, Table} from 'akeneo-design-system';

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

const TitleCell = styled(Table.Cell)`
  font-style: italic;
  color: ${getColor('brand', 100)};
`;

const InputContainer = styled.div`
  max-width: 10vw;
`;

const TwoColumns = styled.div<{withoutSecondColumn: boolean} & AkeneoThemedProps>`
  margin-top: 10px;
  display: grid;
  grid-template-columns: ${({withoutSecondColumn}) => (withoutSecondColumn ? 'auto' : 'auto 300px')};
  grid-template-rows: 1fr;
  grid-column-gap: 30px;
  & > * > * {
    margin-top: 10px;
  }
`;

const TranslationsLabelSkeleton = styled(SkeletonPlaceholder)`
  max-width: 460px;
`;

const TranslationsTextFieldSkeleton = styled(SkeletonPlaceholder)`
  margin-top: 8px;
  max-width: 460px;
  height: 38px;
`;

const CellInputContainer = styled(Table.Cell)`
  width: calc(10vw + 20px);
`;

const NotDraggableCell = styled(Table.Cell)`
  width: 44px;
`;

const TitleCondition = styled(TitleCell)`
  width: calc(10vw + 100px);
`;

const Styled = {
  CellInputContainer,
  FormContainer,
  FullPageCenteredContent,
  InputContainer,
  TitleCell,
  TwoColumns,
  TranslationsLabelSkeleton,
  TranslationsTextFieldSkeleton,
  NotDraggableCell,
  TitleCondition,
};

export {Styled};
