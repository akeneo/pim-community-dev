import React from 'react';
import {Tree} from './Tree';
import {render, screen} from '../../storybook/test-util';

test('it renders tree with children properly', () => {
  render(
    <Tree value={'master'} label={'Master'}>
      <Tree value={'camcorders'} label={'Camcorders'} />
    </Tree>
  );

  expect(screen.getByText('Master')).toBeInTheDocument();
  expect(screen.getByText('Camcorders')).toBeInTheDocument();
});

test('Tree supports forwardRef', () => {
  const ref = {current: null};

  render(<Tree value={'master'} label={'Master'} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tree supports ...rest props', () => {
  render(<Tree value={'master'} label={'Master'} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
