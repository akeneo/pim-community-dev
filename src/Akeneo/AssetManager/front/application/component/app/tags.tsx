import * as React from 'react';
import $ from 'jquery';

export interface TagsProps {
  values: string[] | string;
  tags: string[] | string;
  readOnly: boolean;
  configuration?: any;
  onChange: (value: string[] | string) => void;
}

export default class Tags extends React.Component<TagsProps & any> {
  public props: TagsProps & any;
  private tags: React.RefObject<HTMLSelectElement>;

  constructor(props: TagsProps & any) {
    super(props);

    this.tags = React.createRef<HTMLSelectElement>();
  }

  componentDidMount() {
    if (null === this.tags.current) {
      return;
    }
    const $el = $(this.tags.current) as any;

    if (undefined !== $el.select2) {
      $el.val(this.props.values.join(',')).select2(this.getConfiguration());
      $el.on('change', (event: any) => this.props.onChange(this.cleanExtensions(event.val))); // Still appears to the user :/
    }
  }

  componentDidUpdate() {
    if (null === this.tags.current) {
      return;
    }
    const $el = $(this.tags.current) as any;

    if (undefined !== $el.select2) {
      $el.select2(this.getConfiguration());
    }
  }

  componentWillUnmount() {
    if (null === this.tags.current) {
      return;
    }
    const $el = $(this.tags.current) as any;

    $el.off('change');
  }

  render(): JSX.Element | JSX.Element[] {
    const {configuration, ...props} = this.props;

    return <div {...props} ref={this.tags} disabled={props.readOnly}></div>;
  }
  private getConfiguration() {
    return {
      ...this.props.configuration,
      tags: this.props.tags,
      tokenSeparators: [',', ' '],
      onChange: (params: any) => {
        console.log(params.term);
        return {
          id: params.term,
          text: params.term,
        };
      },
    };
  }

  private cleanExtensions(extensions: string[]) {
    // Remove special characters ?
    // allow only [a-zA-Z0-9]* ?
    return extensions.map((extension: string) => ('.' === extension.charAt(0) ? extension.slice(1) : extension));
  }
}
