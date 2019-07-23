export default (state = null, action) => {
  switch (action.type) {
    case 'GLOBAL_SETTINGS/SAVE':
      return action.data;

    default:
      return state;
  }
};
