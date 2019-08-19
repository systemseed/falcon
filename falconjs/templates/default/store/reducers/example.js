export default (state = {}, action) => {
  switch (action.type) {
    case 'EXAMPLE_REDUCER':
      return action.data;

    default:
      return state;
  }
};
