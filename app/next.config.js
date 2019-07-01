module.exports = {
  webpack: (config) => {
    // Fixes npm packages that depend on `fs` module
    // eslint-disable-next-line no-param-reassign
    config.node = {
      fs: 'empty',
    };

    return config;
  },
};
