import NotEnoughItems from 'akeneoreferenceentity/application/component/record/edit/product/not-enough-items';
import * as React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- NotEnougItems', () => {
  test('Display the NotEnoughItems', () => {
    var selectedAttribute = {
      code: 'front_view',
      labels: {
        en_US: 'Nice front view',
      },
      reference_data_name: 'brand',
      type: 'akeneo_reference_entity',
    };
    const NotEnoughItemsView = mount(
      <NotEnoughItems
        productCount=1
        totalCount=2
        selectedAttribute={selectedAttribute}
        showMore={() => {}}
      />
    );

    expect(NotEnoughItemsView.find('.AknGridContainer-notEnoughDataTitle')).toEqual(true);
  });

  test('Click on a simple Checkbox', () => {
    var value = true;
    const CheckboxView = mount(
      <Checkbox
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
      />
    );

    expect(value).toEqual(true);

    CheckboxView.simulate('click');
    expect(value).toEqual(false);
  });

  test('Do not trigger if read only', () => {
    var value = true;
    const CheckboxView = mount(
      <Checkbox
        value={value}
        onChange={newValue => {
          value = newValue;
        }}
        readOnly={true}
      />
    );

    expect(CheckboxView.find('.AknCheckbox').is('[aria-checked="true"]')).toEqual(true);
    expect(CheckboxView.find('.AknCheckbox').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(true);
  });

  test('Display a simple Checkbox with an id', () => {
    const CheckboxView = mount(<Checkbox value={false} onChange={newValue => {}} id="my_awesome_Checkbox" />);
    expect(CheckboxView.is('#my_awesome_Checkbox')).toEqual(true);
  });

  test('Display a simple Checkbox in read only', () => {
    var value = true;
    const CheckboxView = mount(<Checkbox value={value} readOnly={true} />);

    expect(CheckboxView.find('.AknCheckbox').is('[aria-checked="true"]')).toEqual(true);
    expect(CheckboxView.find('.AknCheckbox').is('[data-checked="true"]')).toEqual(true);
    expect(value).toEqual(true);

    CheckboxView.simulate('keypress', {key: ' '});
    expect(value).toEqual(true);
  });

  test('Non read only Checkbox need an onChange method', () => {
    console.error = jest.fn();

    expect(() => {
      mount(<Checkbox value={true} readOnly={false} />);
    }).toThrow();

    expect(console.error).toHaveBeenCalled();
  });
});
