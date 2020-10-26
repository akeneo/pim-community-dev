import React from 'react';
import {usePopoverState, Popover, PopoverDisclosure} from 'reakit/Popover';
import styled from 'styled-components';
import {useTranslate} from '../../dependenciesTools/hooks';
import {color} from '../../theme';

type Props = {
  predefinedFormats: {[key: string]: string};
  onChange: (dateFormat: string) => void;
  value: string;
  defaultFormat: string;
};

const DateFormatPopover = styled(Popover)`
  background: white;
  width: 300px;
  box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.3);
  overflow: auto;
  z-index: 1;
  padding: 12px 20px 20px 20px;
`;

const PopoverItem = styled.li<{selected: boolean}>`
  cursor: pointer;
  height: 34px;
  line-height: 34px;
  padding: 0 6px;
  color: ${({theme, selected}) =>
    selected ? theme.color.purple100 : 'inherit'};
  background: ${({selected}) => (selected ? '#F9F9FB' : 'inherit')};
  &:hover {
    background: #f9f9fb;
  }
`;

const PopoverArrow = styled.span`
  background: url(/bundles/pimui/images/icon-down.svg) no-repeat 10px 10px;
  width: 40px;
  display: block;
  position: absolute;
  height: 30px;
  display: inline-block;
  background-size: 20px;
`;

export const DateFormatSelector: React.FC<Props> = ({
  predefinedFormats,
  onChange,
  value,
  defaultFormat,
}) => {
  const translate = useTranslate();
  const popover = usePopoverState({gutter: 0, placement: 'top-end'});
  const PopoverButton = (
    <button type='button'>
      {value || defaultFormat}
      <PopoverArrow />
    </button>
  );
  const PopoverButtonProps = {
    style: {
      backgroundColor: 'white',
      border: `1px solid ${color.purple100}`,
      borderRadius: '2px',
      cursor: 'pointer',
      height: '40px',
      left: 0,
      zIndex: 1,
      padding: '0 41px 0 15px',
      margin: '0 10px 0 0',
      display: 'table-cell',
      color: color.grey100,
      borderLeftWidth: 0,
      borderBottomLeftRadius: 0,
      borderTopLeftRadius: 0,
      lineHeight: '40px',
    },
  };

  return (
    <>
      <PopoverDisclosure {...popover}>
        {disclosureProps =>
          React.cloneElement(PopoverButton, {
            ...disclosureProps,
            ...PopoverButtonProps,
          })
        }
      </PopoverDisclosure>
      <DateFormatPopover
        {...popover}
        aria-label={translate(
          'pimee_catalog_rule.form.edit.actions.concatenate.date_format'
        )}>
        <div className='AknDropdown-menuTitle'>
          {translate(
            'pimee_catalog_rule.form.edit.actions.concatenate.date_format'
          )}
        </div>
        <ul>
          {Object.keys(predefinedFormats).map(format => {
            return (
              <PopoverItem
                key={format}
                onClick={() => {
                  onChange(format);
                  popover.hide();
                }}
                selected={(value || defaultFormat) === format}>
                {format}&nbsp;{predefinedFormats[format]}
              </PopoverItem>
            );
          })}
        </ul>
        <input
          placeholder={defaultFormat}
          type='text'
          className='AknTextField'
          defaultValue={value}
          onChange={e => {
            onChange(e.target.value);
          }}
        />
      </DateFormatPopover>
    </>
  );
};
