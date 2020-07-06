import { useFormContext } from 'react-hook-form';
import get from 'lodash/get';
import { LocaleCode, ScopeCode } from '../../../models';
import { Operator } from '../../../models/Operator';

const useControlledFormInputCondition = <T>(lineNumber: number) => {
  const { watch, getValues, setValue } = useFormContext();
  const fieldFormName = `content.conditions[${lineNumber}].field`;
  const operatorFormName = `content.conditions[${lineNumber}].operator`;
  const valueFormName = `content.conditions[${lineNumber}].value`;
  const scopeFormName = `content.conditions[${lineNumber}].scope`;
  const localeFormName = `content.conditions[${lineNumber}].locale`;
  const getOperatorFormValue = (): Operator =>
    get(getValues(), operatorFormName);
  const getValueFormValue = (): T => get(getValues(), valueFormName);
  const getScopeFormValue = (): ScopeCode => get(getValues(), scopeFormName);
  const getLocaleFormValue = (): LocaleCode => get(getValues(), localeFormName);
  const setOperatorFormValue = (data: Operator) =>
    setValue(operatorFormName, data);
  const setValueFormValue = (data?: T) => setValue(valueFormName, data);
  const setScopeFormValue = (data: ScopeCode) => setValue(scopeFormName, data);
  const setLocaleFormValue = (data: LocaleCode) =>
    setValue(localeFormName, data);
  watch(operatorFormName);
  watch(valueFormName);
  watch(scopeFormName);
  watch(localeFormName);

  return {
    fieldFormName,
    getLocaleFormValue,
    getOperatorFormValue,
    getScopeFormValue,
    getValueFormValue,
    localeFormName,
    operatorFormName,
    scopeFormName,
    setLocaleFormValue,
    setOperatorFormValue,
    setScopeFormValue,
    setValueFormValue,
    valueFormName,
  };
};

const useControlledFormInputAction = <T>(lineNumber: number) => {
  const { getValues, setValue } = useFormContext();
  const fieldFormName = `content.actions[${lineNumber}].field`;
  const typeFormName = `content.actions[${lineNumber}].type`;
  const itemsFormName = `content.actions[${lineNumber}].items`;
  const valueFormName = `content.actions[${lineNumber}].value`;
  const scopeFormName = `content.actions[${lineNumber}].scope`;
  const localeFormName = `content.actions[${lineNumber}].locale`;
  const getValueFormValue = (): T => get(getValues(), valueFormName);
  const getItemsFormValue = (): T => get(getValues(), itemsFormName);
  const getFieldFormValue = (): T => get(getValues(), fieldFormName);
  const getScopeFormValue = (): ScopeCode => get(getValues(), scopeFormName);
  const getLocaleFormValue = (): LocaleCode => get(getValues(), localeFormName);
  const setFieldFormValue = (data?: T) => setValue(fieldFormName, data);
  const setItemsFormValue = (data?: T) => setValue(itemsFormName, data);
  const setValueFormValue = (data?: T) => setValue(valueFormName, data);

  return {
    fieldFormName,
    getFieldFormValue,
    getItemsFormValue,
    getLocaleFormValue,
    getScopeFormValue,
    getValueFormValue,
    itemsFormName,
    localeFormName,
    scopeFormName,
    setFieldFormValue,
    setItemsFormValue,
    setValueFormValue,
    typeFormName,
    valueFormName,
  };
};

export { useControlledFormInputCondition, useControlledFormInputAction };
