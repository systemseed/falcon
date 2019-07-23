/* eslint-disable no-console */
const nextjs = require('next');
const dotenv = require('dotenv');
const favicon = require('serve-favicon');
const applyFalconRoutesConfiguration = require('@systemseed/falcon/routes/server.js');
const { globalSettingsForApp, homePageInSettings } = require('@systemseed/falcon/routes/globalSettings');
const decoupledRouter = require('@systemseed/falcon/routes/decoupledRouter');
const clearCache = require('@systemseed/falcon/routes/clearCache');
const { frontendOnlyRoutes } = require('@systemseed/falcon/routes/frontendOnlyRoutes');
const routes = require('./routes/routes');

// Import variables from local .env file.
dotenv.config();

const port = process.env.PORT || 3000;
const dev = process.env.NODE_ENV !== 'production';
const app = nextjs({ dev });

app
  .prepare()
  .then(() => {
    const expressServer = applyFalconRoutesConfiguration(app);
    expressServer.use(favicon(`${__dirname}/static/favicon.ico`));
    expressServer.use(clearCache);
    expressServer.use(globalSettingsForApp(app, process.env.SETTINGS_NAME));
    expressServer.use(homePageInSettings);
    expressServer.use(frontendOnlyRoutes(app, routes));

    // Handle all other requests using our custom router which is a mix
    // or original Next.js logic and Drupal routing logic.
    expressServer.get('*', (req, res) => decoupledRouter(req, res, app));

    expressServer.listen(port, (err) => {
      if (err) throw err;
      console.log(`> Application is ready on ${process.env.FRONTEND_URL}`);
    });
  })
  .catch(err => console.error(err));
