import React, { ChangeEvent } from 'react';
import {
  Select2Option,
  Select2OptionGroup,
  Select2Props,
  Select2Wrapper as BaseWrapper,
} from '../Select2Wrapper';
import { Label } from '../../Labels';
import { httpGet } from '../../../fetch';

const Select2Wrapper: typeof BaseWrapper = ({
  data = [],
  hiddenLabel = false,
  id,
  label,
  onChange,
  value,
  multiple = false,
  placeholder,
  ajax,
  onSelecting,
}: Select2Props) => {
  const [options, setOptions] = React.useState<(Select2Option | Select2OptionGroup)[]>(
    data || []
  );

  const handleClick = () => {
    if (options.length === 0 && ajax) {
      const url = ajax.url;
      const result = httpGet(url);
      if (undefined === result) {
        throw new Error(`You did not mock the result of ${url}!`);
      }
      result.then(response => {
        response.json().then((fetchedOptions: (Select2Option | Select2OptionGroup)[]) => {
          setOptions(fetchedOptions);
        });
      });
    }
  };

  const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
    if (onSelecting) {
      onSelecting({
        preventDefault: event.preventDefault.bind(event),
        val: event.target.value,
      });
    } else if (onChange) {
      onChange(event.target.value);
    }
  };

  const defaultValue = value || (options[0] ? options[0].id : '');

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <select
        id={id}
        data-testid={id}
        defaultValue={(defaultValue || '') as string}
        onChange={handleChange}
        onClick={handleClick}
        multiple={multiple}>
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
