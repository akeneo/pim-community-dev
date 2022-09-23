import React, {Component} from 'react';

type ErroBoundaryProps = {
  children: React.ReactNode;
  fallback?: React.ReactNode;
};

class ErrorBoundary extends Component<ErroBoundaryProps, {error?: Error}> {
  constructor(props: ErroBoundaryProps) {
    super(props);
    this.state = {};
  }

  static getDerivedStateFromError(error: Error) {
    console.error(error);

    return {error};
  }

  render() {
    if (this.state.error) {
      return this.props.fallback ?? <div>An error occured: "{this.state.error?.message}"</div>;
    }

    return this.props.children;
  }
}

export {ErrorBoundary};
