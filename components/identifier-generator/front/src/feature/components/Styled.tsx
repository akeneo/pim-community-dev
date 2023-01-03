import styled from 'styled-components';
import {AkeneoThemedProps, getColor, Helper, SkeletonPlaceholder, Table} from 'akeneo-design-system';

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

const MainErrorHelper = styled(Helper)`
  margin-top: 10px;
`;

const ErrorContainer = styled.div`
  gap: 10px;
  display: flex;
  align-items: center;
  color: ${getColor('grey', 120)};
  font-style: normal;
  margin-left: 10px;
`;

const ErrorList = styled.ul`
  margin: 0;
  padding-left: 20px;
`;

const CellInputContainer = styled(Table.Cell)`
  width: calc(10vw + 20px);
`;

const TranslationsPlaceholderTitleConditions = styled.div`
  font-weight: bold;
`;

// Overriding to not be impacted by the global style
const ListItems = styled.ul`
  margin-bottom: 0;
  margin-block-start: 0;
  padding-inline-start: 40px;
  li {
    list-style: disc;
  }
`;

const NotDraggableCell = styled(Table.Cell)`
  width: 44px;
`;

const TitleCondition = styled(TitleCell)`
  width: calc(10vw + 100px);
`;

const Styled = {
  CellInputContainer,
  ErrorContainer,
  ErrorList,
  MainErrorHelper,
  FormContainer,
  FullPageCenteredContent,
  InputContainer,
  TitleCell,
  TwoColumns,
  TranslationsLabelSkeleton,
  TranslationsTextFieldSkeleton,
  TranslationsPlaceholderTitleConditions,
  NotDraggableCell,
  TitleCondition,
  ListItems,
};

export {Styled};
