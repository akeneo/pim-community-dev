import React, { useState, useEffect } from 'react';
import { useMenuState, Menu, MenuItem, MenuButton } from 'reakit/Menu';
import { Input, InputProps } from './Input';
import { Translate } from '../../dependenciesTools';
import styled from 'styled-components';

type TimePeriodId = string;

type TimePeriodOption = {
  id: TimePeriodId;
  getText: (nb: number) => string;
};

type InputRelativeDateProps = {
  currentTimePeriod: TimePeriodId;
  currentTimeValue: string;
  lineNumber: number;
  onPeriodChange?: (timePeriod: any, timeValue: string) => void;
  timePeriodOptions: TimePeriodOption[];
  translateLabel: Translate;
} & InputProps;

const InputRelativeDateCtn = styled.div`
  position: relative;
`;

const DateOptionsContainer = styled.ul`
  align-items: center;
  background: white;
  box-shadow: 0 4px 5px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  min-width: 150px;
  padding-bottom: 20px;
  width: auto;
  z-index: 2;
`;

// Color is not present in theme ATM (discussed with the UI team)
const TimePeriodButtonContent = styled.div`
  align-items: center;
  color: #515d6d;
  display: flex;
  height: 40px;
  padding-left: 10px;
  position: absolute;
  right: 10px;
  text-transform: uppercase;
  top: 0;
`;

const TimePeriodText = styled.div`
  padding-right: 10px;
`;
const TimePeriodImg = styled.img`
  height: 20px;
`;

const StyledMenuButton = styled(MenuButton)`
  background: transparent;
  border: none;
  height: 40px;
  position: absolute;
  right: 0;
  top: 0;
  width: 38px;
`;

const StyledMenuItem = styled(MenuItem)`
  background: ${({ theme }) => theme.color.white};
  border: none;
  color: ${({ theme }) => theme.color.grey120};
  height: inherit;
  margin-bottom: 2px 0;
  padding: 0 20px;
  text-align: left;
  text-transform: capitalize;
  width: inherit;
`;

const StyledOptionLi = styled.li`
  height: 34px;
  margin: 2px 0;
  width: 100%;
`;

const StyledMenu = styled(Menu)`
  border-right: 1px solid rgb(204, 209, 216);
  border-bottom: 1px solid rgb(204, 209, 216);
  border-left: 1px solid rgb(204, 209, 216);
  border-top: none;
  border-radius: 0px 0px 2px 2px;
  z-index: 2;
`;

const getTimePeriodTextFromId = (
  timePeriodOptions: TimePeriodOption[],
  timePeriodSelected: TimePeriodId,
  timeValue: string
) => {
  const timePeriod = timePeriodOptions.find(
    ({ id }) => id === timePeriodSelected
  );
  if (!timePeriod) {
    return null;
  }
  if (isNaN(Number.parseInt(timeValue))) {
    return timePeriod.getText(1);
  }
  return timePeriod.getText(Number.parseInt(timeValue));
};

const InputRelativeDate = React.forwardRef<
  HTMLInputElement,
  InputRelativeDateProps
>(
  (
    {
      currentTimePeriod,
      currentTimeValue,
      lineNumber,
      onPeriodChange,
      timePeriodOptions,
      translateLabel,
      ...rest
    },
    forwardedRef: React.Ref<HTMLInputElement>
  ) => {
    const menu = useMenuState({ wrap: 'vertical' });
    const [timePeriodSelected, setTimePeriodSelected] = useState<TimePeriodId>(
      currentTimePeriod
    );
    const [timeValue, setTimeValue] = useState<string>(currentTimeValue);

    const handleOnClick = (timePeriod: TimePeriodId) => () => {
      setTimePeriodSelected(timePeriod);
      if (onPeriodChange) {
        onPeriodChange(timePeriod, timeValue);
      }
      menu.hide();
    };

    const handleOnChangeTimeValue = (
      event: React.ChangeEvent<HTMLInputElement>
    ) => {
      const numericalValue = event.target.value.match(/\d+/g);
      let value = '';
      if (Array.isArray(numericalValue)) {
        value = numericalValue.join('');
      }
      setTimeValue(value);
      if (onPeriodChange) {
        onPeriodChange(timePeriodSelected, value);
      }
    };

    useEffect(() => {
      setTimeValue(currentTimeValue);
      setTimePeriodSelected(currentTimePeriod);
    }, [currentTimeValue, currentTimePeriod]);

    return (
      <InputRelativeDateCtn>
        <Input
          className='AknTextField'
          hiddenLabel
          id={`input-relative-date-${lineNumber}`}
          label={translateLabel(
            'pimee_catalog_rule.form.date.label.relative_date'
          )}
          maxLength={2}
          onChange={handleOnChangeTimeValue}
          ref={forwardedRef}
          type='text'
          value={timeValue}
          {...rest}
        />
        <StyledMenuButton aria-haspopup='listbox' {...menu}>
          <TimePeriodButtonContent>
            <TimePeriodText data-testid='time-period-selected'>
              {getTimePeriodTextFromId(
                timePeriodOptions,
                timePeriodSelected,
                timeValue
              )}
            </TimePeriodText>
            <TimePeriodImg
              src='bundles/pimui/images/jstree/icon-down.svg'
              alt={translateLabel(
                'pimee_catalog_rule.form.date.label.period_menu'
              )}
            />
          </TimePeriodButtonContent>
        </StyledMenuButton>
        <StyledMenu
          {...menu}
          aria-label={translateLabel(
            'pimee_catalog_rule.form.date.label.period'
          )}
          style={{ transform: 'translate3d(0px, 39px, 0px)' }}>
          <DateOptionsContainer tabIndex={-1} role='listbox'>
            {timePeriodOptions.map(dateTimeOpt => (
              <StyledOptionLi key={dateTimeOpt.id} role='option'>
                <StyledMenuItem
                  {...menu}
                  as='button'
                  onClick={handleOnClick(dateTimeOpt.id)}>
                  {dateTimeOpt.getText(Number.parseInt(timeValue))}
                </StyledMenuItem>
              </StyledOptionLi>
            ))}
          </DateOptionsContainer>
        </StyledMenu>
      </InputRelativeDateCtn>
    );
  }
);

InputRelativeDate.displayName = 'InputRelativeDate';

export { InputRelativeDate };
