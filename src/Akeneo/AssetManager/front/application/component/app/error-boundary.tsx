import * as React from 'react';

type Props = {errorMessage: string};

export default class ErrorBoundary extends React.Component<Props, {hasError: boolean; error: Error | null}> {
  constructor(props: Props) {
    super(props);
    this.state = {hasError: false, error: null};
  }

  static getDerivedStateFromError(error: Error) {
    return {hasError: true, error};
  }

  componentDidCatch(error: Error | null) {
    this.setState({
      hasError: true,
      error: error || new Error(this.props.errorMessage),
    });
  }

  render() {
    if (this.state.hasError && null !== this.state.error) {
      return <div>{this.state.error.message}</div>;
    }

    return this.props.children;
  }
}
