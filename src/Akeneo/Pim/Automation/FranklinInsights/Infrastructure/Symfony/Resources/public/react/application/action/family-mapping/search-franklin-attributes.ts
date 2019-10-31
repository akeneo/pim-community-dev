import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';

export function resetGridFilters() {
  return (dispatch: any) => {
    dispatch(updateCodeOrLabelFilter());
    dispatch(updateStatusFilter(null));
  };
}

export const UPDATE_CODE_OR_LABEL_FILTER = 'UPDATE_CODE_OR_LABEL_FILTER';

export interface UpdateCodeOrLabelFilterAction {
  type: typeof UPDATE_CODE_OR_LABEL_FILTER;
  codeOrLabel?: string;
}

export function updateCodeOrLabelFilter(codeOrLabel?: string): UpdateCodeOrLabelFilterAction {
  return {
    type: UPDATE_CODE_OR_LABEL_FILTER,
    codeOrLabel
  };
}

export const UPDATE_STATUS_FILTER = 'UPDATE_STATUS_FILTER';

export interface UpdateStatusFilterAction {
  type: typeof UPDATE_STATUS_FILTER;
  status: AttributeMappingStatus | null;
}

export function updateStatusFilter(status: AttributeMappingStatus | null): UpdateStatusFilterAction {
  return {
    type: UPDATE_STATUS_FILTER,
    status
  };
}

export type SearchFranklinAttributesActions = UpdateCodeOrLabelFilterAction | UpdateStatusFilterAction;
