export const getTextInputClassName = (canEdit: boolean): string => {
  return `AknTextField AknTextField--light
      ${!canEdit ? 'AknTextField--disabled' : ''}
    `;
};
