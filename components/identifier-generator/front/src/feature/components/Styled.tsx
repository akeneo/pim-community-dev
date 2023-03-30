import styled from 'styled-components';
import {
  AkeneoThemedProps,
  Button,
  Dropdown,
  getColor,
  Helper,
  MultiSelectInput,
  Preview as PreviewComponent,
  SelectInput,
  SkeletonPlaceholder,
  Table,
  TextInput,
} from 'akeneo-design-system';
import {TEXT_TRANSFORMATION, TextTransformation} from '../models';

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 20px;
`;

const PropertyFormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 10px;
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
  vertical-align: top;
  line-height: 40px;
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
    align-items: flex-start;
  }
`;

const inputWidth = '400px';

const MultiSelectCondition = styled(MultiSelectInput)`
  flex-basis: ${inputWidth};
`;
const CategoriesDropdownContainer = styled(Dropdown)`
  width: ${inputWidth};
`;

const SingleSelectCondition = styled(SelectInput)`
  flex-basis: ${inputWidth};
`;

const OperatorSelectCondition = styled(SelectInput)<{isInSelection: boolean}>`
  ${props => props.isInSelection && 'flex-basis: 160px;'}
`;

const SelectCondition = styled(SelectInput)<{isHorizontal: boolean}>`
  ${({isHorizontal}) => isHorizontal && 'flex-basis: 150px;'}
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

const NomenclatureModalContent = styled.div`
  width: calc(100vw - 240px);
  height: calc(100vh - 160px);
  display: flex;
  flex-direction: column;
`;

const NomenclatureDefinition = styled(Table)`
  width: 100%;
  margin-bottom: 20px;
  td:nth-child(3),
  td:nth-child(2) {
    width: 250px;
  }
  label {
    font-size: inherit;
  }
`;

const NomenclatureInput = styled(TextInput)`
  max-width: 200px;
`;

const NomenclatureTable = styled.div`
  td:nth-child(2) {
    width: 1px;
    padding-right: 20px;
  }
`;

const NomenclatureContent = styled.div`
  height: 100%;
  overflow: auto;
`;

const CategoryTreeContainer = styled.div`
  max-height: 400px;
  overflow: auto;
  margin: 0 20px 0 0;
  padding: 0 0 0 20px;
  ul {
    padding-left: 0;
  }
  & > *:first-child {
    margin-top: 0;
    margin-bottom: 0;
  }
`;

const NomenclatureButton = styled(Button)`
  width: fit-content;
`;

const Styled = {
  BoldContainer,
  CellInputContainer,
  CheckboxContainer,
  ConditionLineSkeleton,
  ErrorContainer,
  FormContainer,
  FullPageCenteredContent,
  InputContainer,
  ListItems,
  MainErrorHelper,
  MultiSelectCondition,
  OperatorContainer,
  OperatorSelectCondition,
  PropertyFormContainer,
  SelectCondition,
  SelectionInputsContainer,
  SingleSelectCondition,
  TitleCell,
  TranslationsLabelSkeleton,
  TranslationsTextFieldSkeleton,
  TwoColumns,
  PreviewWithTextTransformation,
  NomenclatureModalContent,
  NomenclatureContent,
  NomenclatureDefinition,
  NomenclatureInput,
  NomenclatureTable,
  NomenclatureButton,
  CategoryTreeContainer,
  CategoriesDropdownContainer,
};

export {Styled};
