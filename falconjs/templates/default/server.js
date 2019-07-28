/* eslint-disable no-console */
const nextjs = require('next');
const dotenv = require('dotenv');
const favicon = require('serve-favicon');
const applyFalconRoutingConfiguration = require('@systemseed/falcon/routing/server.js');
const { globalSettingsForApp, handleHomepageRequest } = require('@systemseed/falcon/routing/globalSettings');
const decoupledRouter = require('@systemseed/falcon/routing/decoupledRouter');
const clearCache = require('@systemseed/falcon/routing/clearCache');
const frontendOnlyRoutes = require('@systemseed/falcon/routing/frontendOnlyRoutes');
const routes = require('./routes/routes');

// Import variables from local .env file.
dotenv.config();

const port = process.env.PORT || 3000;
const dev = process.env.NODE_ENV !== 'production';
const app = nextjs({ dev });

app
  .prepare()
  .then(() => {
    const expressServer = applyFalconRoutingConfiguration(app);
    expressServer.use(favicon(`${__dirname}/static/favicon.ico`));
    expressServer.use('/_clear', clearCache);
    expressServer.use(globalSettingsForApp(app, process.env.APPLICATION_NAME));
    expressServer.use(handleHomepageRequest);
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
