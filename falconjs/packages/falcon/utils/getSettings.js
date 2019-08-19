const debug = require('debug')('falconjs:utils/getSettings');
const { request: defaultRequest } = require('../request/request.node.js');

/**
 * Loads global site settings (menu, header, footer) from Drupal backend.
 */
async function getSettings(settingsName, request = null, res = null) {
  const props = {
    statusCode: null,
    settings: null,
  };

  const superagent = request || defaultRequest;

  debug(`Requesting Global settings from the Drupal backend with name ${settingsName}...`);
  const settingsResponse = await superagent
    .get(`/config_pages/${settingsName}`)
    // Tell superagent to consider any valid Drupal response as successful.
    // Later we can capture error codes if needed.
    .ok(response => response.statusCode)
    .query({ _format: 'json_recursive' });

  // Catch all 40x and 50x errors during request to fetch global settings.
  if (settingsResponse.statusCode >= 400) {
    if (res) res.status(settingsResponse.statusCode);
    props.statusCode = settingsResponse.statusCode;
    debug('Global settings request to the backend caught %s error. Response: %o', settingsResponse.statusCode, settingsResponse);
  }

  // Get global settings object from the request and pass it to the
  // pages as a part of server response object.
  if (settingsResponse.statusCode === 200) {
    props.settings = settingsResponse.body;
    props.statusCode = 200;
  }

  return props;
}

module.exports = getSettings;
