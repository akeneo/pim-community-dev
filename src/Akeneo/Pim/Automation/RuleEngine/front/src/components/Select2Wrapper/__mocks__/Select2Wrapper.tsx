import React, { ChangeEvent, useEffect } from 'react';
import {
  Select2Option,
  Select2OptionGroup,
  Select2Props, Select2Value,
  Select2Wrapper as BaseWrapper,
} from '../Select2Wrapper';
import { Label } from '../../Labels';
import { httpGet } from '../../../fetch';

const Select2Wrapper: typeof BaseWrapper = ({
  data,
  hiddenLabel = false,
  id,
  label,
  multiple = false,
  placeholder,
  ajax,
  onValueChange,
  value,
  onSelecting,
}: Select2Props) => {
  const [stateOptions, setOptions] = React.useState<
    (Select2Option | Select2OptionGroup)[]
  >(data || []);

  /** FU */
  useEffect(() => {
    if (data) {
      setOptions(data);
    }
  }, [ data ]);

  const options = stateOptions;
  if (value) {
    const values = Array.isArray(value) ? value : [value];
    values.forEach((valueMesCouilles: Select2Value) => {
      if (!stateOptions.map(option => option.id).includes(valueMesCouilles)) {
        options.push({id: valueMesCouilles, text: `__mock__${valueMesCouilles}`});
      }
    });
  }

  const handleClick = () => {
    if (ajax) {
      const url = ajax.url;
      const result = httpGet(url);
      if (undefined === result) {
        throw new Error(`You did not mock the result of ${url}!`);
      }
      result.then(response => {
        response
          .json()
          .then((fetchedOptions: (Select2Option | Select2OptionGroup)[]) => {
            setOptions(fetchedOptions);
          });
      });
    }
  };

  const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
    if (onValueChange) {
      onValueChange(event.target.value);
    }
    if (onSelecting) {
      onSelecting({
        preventDefault: event.preventDefault.bind(event),
        val: event.target.value,
      });
    }
  };

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <select
        id={id}
        data-testid={id}
        onChange={handleChange}
        onClick={handleClick}
        multiple={multiple}
        value={value === null ? (multiple ? [] : '') : value as string}
      >
        {placeholder ? (
          <option disabled value={''}>
            {placeholder}
          </option>
        ) : (
          ''
        )}
        {options.map((option: Select2Option | Select2OptionGroup, i) => {
          return option.hasOwnProperty('children') ? (
            <optgroup key={option.id || i} label={option.text}>
              {(option as Select2OptionGroup).children.map((subOption, j) => (
                <option key={subOption.id || j} value={subOption.id || j}>
                  {subOption.text}
                </option>
              ))}
            </optgroup>
          ) : (
            <option key={option.id || i} value={option.id || i}>
              {option.text}
            </option>
          );
        })}
      </select>
    </>
  );
};

export { Select2Wrapper };
