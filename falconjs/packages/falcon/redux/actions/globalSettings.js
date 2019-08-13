/**
 * Saves global settings in the global redux object.
 */
export const save = settings => ({
  type: 'GLOBAL_SETTINGS/SAVE',
  data: settings,
});
