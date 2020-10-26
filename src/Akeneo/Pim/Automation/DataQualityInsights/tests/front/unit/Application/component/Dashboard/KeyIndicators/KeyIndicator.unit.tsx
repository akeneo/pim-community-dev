import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithAppContextHelper} from "../../../../../utils/render";
import {KeyIndicator} from "../../../../../../../front/src/application/component/Dashboard";
import {tips} from "../../../../../utils/provider/provideKeyIndicatorsHelper";

test('It displays a key indicator with products to work on', () => {
  const {getByText} = renderWithAppContextHelper(
    <KeyIndicator tips={tips} title={'My key indicator'} total={156412} ratio={22}>
      <span>an_icon</span>
    </KeyIndicator>
    );

  expect(getByText('an_icon')).toBeInTheDocument();
  expect(getByText('My key indicator')).toBeInTheDocument();
  expect(getByText('22%')).toBeInTheDocument();
  expect(getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on')).toBeInTheDocument();
  expect(getByText(/first_step_message/)).toBeInTheDocument();
});

test('It displays a key indicator with no product to work on', () => {
  const {getByText, queryByText} = renderWithAppContextHelper(<KeyIndicator tips={tips} title={'My key indicator'} total={0} ratio={100}/>);

  expect(getByText('My key indicator')).toBeInTheDocument();
  expect(getByText('100%')).toBeInTheDocument();
  expect(queryByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on')).toBeNull();
  expect(getByText(/perfect_score_step_message/)).toBeInTheDocument();
});
