import React from 'react';
import {TemplateFormAction, TemplateFormState, templateFormReducer} from '../reducers/templateFormReducer';

const TemplateFormContext = React.createContext<[TemplateFormState, React.Dispatch<TemplateFormAction>] | null>(null);

export const useTemplateForm = () => {
  const context = React.useContext(TemplateFormContext);
  if (null === context) {
    throw new Error('useTemplateForm must be used within a TemplateFormProvider');
  }

  return context;
};

type Props = {
  children: React.ReactNode;
};

export const TemplateFormProvider = ({children}: Props) => {
  const [state, dispatch] = React.useReducer(templateFormReducer, {attributes: {}, properties: {labels: {}}});

  return <TemplateFormContext.Provider value={[state, dispatch]}>{children}</TemplateFormContext.Provider>;
};
