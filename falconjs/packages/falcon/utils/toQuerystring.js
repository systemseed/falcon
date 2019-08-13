/**
 * Converts object of URL query values into query string
 * valid for the browser.
 */
const toQuerystring = obj => Object.keys(obj)
  .filter(key => obj[key] !== null && obj[key] !== undefined)
  .map((key) => {
    let value = obj[key];

    if (Array.isArray(value)) {
      value = value.join('/');
    }
    return [
      encodeURIComponent(key),
      encodeURIComponent(value),
    ].join('=');
  }).join('&');

module.exports = toQuerystring;
