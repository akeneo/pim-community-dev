import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {
  SearchFranklinAttributesActions,
  UPDATE_CODE_OR_LABEL_FILTER,
  UPDATE_STATUS_FILTER,
  UpdateCodeOrLabelFilterAction,
  UpdateStatusFilterAction
} from '../../action/family-mapping/search-franklin-attributes';
import {createReducer} from '../../../infrastructure/create-reducer';

export interface SearchFranklinAttributesState {
  codeOrLabel?: string;
  status: AttributeMappingStatus | null;
}

const initialState: SearchFranklinAttributesState = {
  codeOrLabel: undefined,
  status: null
};

const updateCodeOrLabel = (
  state: SearchFranklinAttributesState,
  action: UpdateCodeOrLabelFilterAction
): SearchFranklinAttributesState => ({
  ...state,
  codeOrLabel: action.codeOrLabel
});

const updateStatus = (
  state: SearchFranklinAttributesState,
  action: UpdateStatusFilterAction
): SearchFranklinAttributesState => ({
  ...state,
  status: action.status
});

export default createReducer<SearchFranklinAttributesState, SearchFranklinAttributesActions>(initialState, {
  [UPDATE_CODE_OR_LABEL_FILTER]: updateCodeOrLabel,
  [UPDATE_STATUS_FILTER]: updateStatus
});
