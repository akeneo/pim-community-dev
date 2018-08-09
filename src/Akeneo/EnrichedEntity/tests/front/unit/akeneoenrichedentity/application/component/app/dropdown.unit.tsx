import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';
import * as React from 'react';
import {mount} from 'enzyme';

const elements = [
  {
    identifier: 'nice_item',
    label: 'actually this is the first item',
  },
  {
    identifier: 'another_item',
    label: 'and this is the second item',
  },
];

describe('>>>COMPONENT --- dropdown', () => {
  test('Display a simple dropdown with a label', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );
    expect(
      dropdown
        .find('.AknActionButton-highlight')
        .text()
        .trim()
    ).toEqual('and this is the second item');
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
  });

  test('Changes according to props updates', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );
    expect(
      dropdown
        .find('.AknActionButton-highlight')
        .text()
        .trim()
    ).toEqual('and this is the second item');
    dropdown.setProps({selectedElement: 'nice_item'})
    expect(
      dropdown
        .find('.AknActionButton-highlight')
        .text()
        .trim()
    ).toEqual('actually this is the first item');
  });

  test('Opens on click, list options and change value on selection', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknButton').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
  });

  test('Closes when backdrop is clicked', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknButton').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);
    dropdown.find('.AknDropdown-mask').simulate('click');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
  });

  test('Opens on keypress on space, list options and change value on selection', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknButton').simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
  });

  test('doesn\'t open on keypress on other key, list options and change value on selection', () => {
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        selectedElement={'another_item'}
      />
    );

    expect(
      dropdown
        .find('.AknDropdown-menuTitle')
        .text()
        .trim()
    ).toEqual('my dropdown');
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknButton').simulate('keypress', {key: ''});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(0);
    dropdown.find('.AknButton').simulate('keypress', {key: ' '});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);

    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('keypress', {key: ''});
    expect(dropdown.find('.AknDropdown-menu--open').length).toEqual(1);
  });

  test('Change value on selection change', () => {
    let value = 'another_item';
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={(newValue: DropdownElement) => {
          value = newValue.identifier;
        }}
        selectedElement={value}
      />
    );

    expect(value).toEqual('another_item');
    dropdown.find('.AknButton').simulate('click');
    dropdown
      .find('.AknDropdown-menuLink')
      .first()
      .simulate('click');
    expect(value).toEqual('nice_item');
  });

  test('No update on value if same value', () => {
    let value = 'my_custom_value';
    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={(newValue: DropdownElement) => {
          value = newValue.identifier;
        }}
        selectedElement={'another_item'}
      />
    );

    expect(value).toEqual('my_custom_value');
    dropdown.find('.AknButton').simulate('click');
    dropdown
      .find('.AknDropdown-menuLink--active')
      .first()
      .simulate('click');
    expect(value).toEqual('my_custom_value');
  });

  test("Doesn't display anything if no elements passed", () => {
    const dropdown = mount(
      <Dropdown elements={[]} label={'my dropdown'} onSelectionChange={() => {}} selectedElement={'item'} />
    );

    expect(dropdown.find('.AknDropdown').length).toEqual(1);
    expect(dropdown.find('.AknDropdown-menuLink').length).toEqual(0);
  });

  test('Uses the given custom item view', () => {
    const ItemView = ({element}: {element: DropdownElement}) => {
      return <li>{element.label}</li>;
    };

    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        ItemView={ItemView}
        selectedElement={'another_item'}
      />
    );

    expect(dropdown.find('li').length).toEqual(2);
  });

  test('Uses the given custom button view', () => {
    const ButtonView = ({label}: {label: string}) => {
      return <span className="myCustomClass">{label}</span>;
    };

    const dropdown = mount(
      <Dropdown
        elements={elements}
        label={'my dropdown'}
        onSelectionChange={() => {}}
        ButtonView={ButtonView}
        selectedElement={'another_item'}
      />
    );

    expect(dropdown.find('.myCustomClass').length).toEqual(1);
    expect(dropdown.find('.myCustomClass').text()).toEqual('my dropdown');
  });
});
