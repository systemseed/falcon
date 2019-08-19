import React from 'react';
import PropTypes from 'prop-types';
import Head from 'next/head';
import he from 'he';

const HtmlHead = ({ metatags, pageTitle }) => {
  let title = pageTitle;
  const metatagsComponents = metatags.map((metatag, index) => {
    const Tag = metatag.tag;
    const decodedContent = {};

    // Grab the page title from the metatags.
    if (metatag.attributes.hasOwnProperty('name') && metatag.attributes.hasOwnProperty('content')) {
      if (Tag === 'meta' && metatag.attributes.name === 'title') {
        title = metatag.attributes.content;
      }
    }

    // Decode Unicode character representation to avoid social media referrer preview parsing
    // issues for URLs but ONLY if it's a meta tag, as some tags like <link> will interpret React
    // props as string literals, even if they are null or undefined
    if (Tag === 'meta' && metatag.attributes.content) {
      decodedContent.content = he.decode(String(metatag.attributes.content));
    }

    return (
      <Tag
        key={`metatag-${index}`}
        {...metatag.attributes}
        {...decodedContent}
      />
    );
  });

  return (
    <Head>
      <title>{title}</title>
      <meta charSet="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta httpEquiv="X-UA-Compatible" content="IE=edge" />
      {metatagsComponents}

      <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />
    </Head>
  );
};

HtmlHead.propTypes = {
  metatags: PropTypes.arrayOf(PropTypes.shape({
    tag: PropTypes.string.isRequired,
    attributes: PropTypes.shape({
      name: PropTypes.string,
      property: PropTypes.string,
      content: PropTypes.string,
    }).isRequired,
  })),
  pageTitle: PropTypes.string,
};

HtmlHead.defaultProps = {
  metatags: [],
  pageTitle: 'Falcon',
};

export default HtmlHead;
