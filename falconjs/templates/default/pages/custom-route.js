import React from 'react';
import Link from 'next/link';

const CustomRoute = () => (
  <div>
    <br />
    <Link as="/demo_page2" href="/node/page" prefetch>
      <a>Page2</a>
    </Link>
    <br />
    <Link as="/cookie-table" href="/custom-route" prefetch>
      <a>Cookie-table 1</a>
    </Link>
    <br />
    <Link as="/cookie-table2" href="/custom-route" prefetch>
      <a>Cookie-table 2</a>
    </Link>
  </div>
);

export default CustomRoute;
