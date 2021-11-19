import React from 'react';
import {render} from '../../storybook/test-util';
import {KeyFigure, KeyFigureGrid} from './KeyFigure';
import {ComponentIcon} from '../../icons';

test('It renders a KeyFigure component with multiple values', () => {
  const {container, getByText} = render(
    <KeyFigure icon={<ComponentIcon />} title="My title">
      <KeyFigure.Figure label="Average:">10</KeyFigure.Figure>
      <KeyFigure.Figure label="Max:">15</KeyFigure.Figure>
    </KeyFigure>
  );
  expect(getByText('My title')).toBeInTheDocument();
  expect(container).toHaveTextContent('Average: 10');
  expect(container).toHaveTextContent('Max: 15');
});

test('It renders a KeyFigure component with only one figure', () => {
  const {getByText} = render(
    <KeyFigure icon={<ComponentIcon />} title="My title">
      <KeyFigure.Figure>1234</KeyFigure.Figure>
    </KeyFigure>
  );
  expect(getByText('My title')).toBeInTheDocument();
  expect(getByText('1234')).toBeInTheDocument();
});

test('Key Figures can be rendered in a grid', () => {
  const {container, getByText} = render(
    <KeyFigureGrid>
      <KeyFigure icon={<ComponentIcon />} title="Key figure 1">
        <KeyFigure.Figure label="Average:">10</KeyFigure.Figure>
        <KeyFigure.Figure label="Max:">15</KeyFigure.Figure>
      </KeyFigure>
      <KeyFigure icon={<ComponentIcon />} title="Key figure 2">
        <KeyFigure.Figure label="Average:">23</KeyFigure.Figure>
        <KeyFigure.Figure label="Max:">45</KeyFigure.Figure>
      </KeyFigure>
      <KeyFigure icon={<ComponentIcon />} title="Key figure 3">
        <KeyFigure.Figure label="Average:">56</KeyFigure.Figure>
        <KeyFigure.Figure label="Max:">78</KeyFigure.Figure>
      </KeyFigure>
    </KeyFigureGrid>
  );

  expect(getByText('Key figure 1')).toBeInTheDocument();
  expect(container).toHaveTextContent('Average: 10');
  expect(container).toHaveTextContent('Max: 15');

  expect(getByText('Key figure 2')).toBeInTheDocument();
  expect(container).toHaveTextContent('Average: 23');
  expect(container).toHaveTextContent('Max: 45');

  expect(getByText('Key figure 3')).toBeInTheDocument();
  expect(container).toHaveTextContent('Average: 56');
  expect(container).toHaveTextContent('Max: 78');
});
