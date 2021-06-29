export interface PageContextState {
  currentTab: string | null;
}

export interface ProductEditFormPageContextState extends PageContextState {
  attributesTabIsLoading: boolean;
  attributeToImprove: string | null;
}
