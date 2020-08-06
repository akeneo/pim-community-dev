import {PageContextState} from "@akeneo-pim-community/data-quality-insights/src/application/state/PageContextState";

export interface AttributeEditFormPageContextState extends PageContextState {

}

export default interface AttributeEditFormState {
  pageContext: AttributeEditFormPageContextState;
}
