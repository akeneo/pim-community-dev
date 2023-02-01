import styled from 'styled-components';
import {
  AkeneoThemedProps,
  getColor,
  Helper,
  MultiSelectInput,
  Preview as PreviewComponent,
  SelectInput,
  SkeletonPlaceholder,
  Table,
} from 'akeneo-design-system';
import {TEXT_TRANSFORMATION, TextTransformation} from '../models';

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 20px;
`;

const EditionContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
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

const TitleCell = styled(Table.Cell)<{withWidth: boolean} & AkeneoThemedProps>`
  font-style: italic;
  color: ${getColor('brand', 100)};
  ${({withWidth = true}) => withWidth && 'width: 120px;'}
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
  ul {
    margin: 0;
    padding-left: 20px;
  }
  ul:has(li:only-child) {
    padding: 0;
  }
  li:only-child {
    margin: 0;
    list-style-type: none;
  }
`;

const ErrorContainer = styled.div`
  gap: 10px;
  display: flex;
  align-items: center;
  color: ${getColor('grey', 120)};
  font-style: normal;
  margin-left: 10px;
`;

const ConditionLineSkeleton = styled(SkeletonPlaceholder)`
  width: 100%;
  height: 40px;
`;

const CellInputContainer = styled(Table.Cell)`
  width: calc(10vw + 20px);
`;

const BoldContainer = styled.div`
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

const SelectionInputsContainer = styled(Table.Cell)`
  > div:first-child {
    display: flex;
    gap: 20px;
  }
`;

const MultiSelectCondition = styled(MultiSelectInput)`
  flex-basis: 300px;
`;

const OperatorSelectCondition = styled(SelectInput)<{isInSelection: boolean}>`
  ${props => props.isInSelection && 'flex-basis: 160px;'}
`;

const SelectCondition = styled(SelectInput)`
  flex-basis: 120px;
`;

const SingleSelectCondition = styled(SelectInput)`
  flex-basis: 300px;
`;

const OperatorContainer = styled.div`
  max-width: 160px;
`;

const CheckboxContainer = styled.div`
  margin: 10px 0;
`;

const PreviewWithTextTransformation = styled(PreviewComponent)<
  {textTransformation: TextTransformation} & AkeneoThemedProps
>`
  ${({textTransformation}) => (textTransformation === TEXT_TRANSFORMATION.LOWERCASE ? 'text-transform: lowercase' : '')}
  ${({textTransformation}) => (textTransformation === TEXT_TRANSFORMATION.UPPERCASE ? 'text-transform: uppercase' : '')}
`;

const Styled = {
  BoldContainer,
  CellInputContainer,
  CheckboxContainer,
  ConditionLineSkeleton,
  EditionContainer,
  ErrorContainer,
  FormContainer,
  FullPageCenteredContent,
  InputContainer,
  ListItems,
  MainErrorHelper,
  MultiSelectCondition,
  OperatorContainer,
  OperatorSelectCondition,
  SelectCondition,
  SelectionInputsContainer,
  SingleSelectCondition,
  TitleCell,
  TranslationsLabelSkeleton,
  TranslationsTextFieldSkeleton,
  TwoColumns,
  PreviewWithTextTransformation,
};

export {Styled};
