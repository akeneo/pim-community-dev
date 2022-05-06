import {render, screen} from '@testing-library/react';
import React from 'react';
import {QualityScoreBar} from '@akeneo-pim-community/data-quality-insights';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

test('it renders quality score bar with A score selected', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScoreBar score={'A'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-bar')).toBeInTheDocument();
  expect(screen.getByText('A')).toHaveStyle({height: '25px', width: '25px'});
  expect(screen.getByText('B')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('C')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('D')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('E')).toHaveStyle({height: '20px', width: '20px'});
});

test('it renders quality score bar with A stacked score selected', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScoreBar score={'A'} stacked />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-bar')).toBeInTheDocument();
  expect(screen.getByText('A')).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '2px',
    left: '2px',
    height: '25px',
    width: '25px',
  });
  const emptyContainerMiddle = screen.getByTestId('empty-container-middle');
  expect(emptyContainerMiddle).toBeInTheDocument();
  expect(emptyContainerMiddle).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '0',
    left: '2px',
  });
  const emptyContainerBack = screen.getByTestId('empty-container-back');
  expect(emptyContainerBack).toBeInTheDocument();
  expect(emptyContainerBack).toHaveStyle({
    border: `1px solid ${pimTheme.color.green60}`,
    top: '-2px',
    left: '4px',
  });

  expect(screen.getByText('B')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('C')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('D')).toHaveStyle({height: '20px', width: '20px'});
  expect(screen.getByText('E')).toHaveStyle({height: '20px', width: '20px'});
});

test('it renders quality score bar rounded at left and at right when B is selected score', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScoreBar score={'B'} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-bar')).toBeInTheDocument();
  expect(screen.getByText('A')).toHaveStyle({borderRadius: '4px 0 0 4px'});
  expect(screen.getByText('B')).toHaveStyle({borderRadius: '4px'});
  expect(screen.getByText('C')).toHaveStyle({borderRadius: '0'});
  expect(screen.getByText('D')).toHaveStyle({borderRadius: '0'});
  expect(screen.getByText('E')).toHaveStyle({borderRadius: '0 4px 4px 0'});
});
