import React from 'react';
import Link from 'next/link';

const CustomRoute = () => (
  <div>
    Custom route
    <Link url="/" as="/" prefetch>
      <a>Home</a>
    </Link>
  </div>
);

export default CustomRoute;
