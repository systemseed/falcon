
const normalizers = [];

export default (url) => {
  let resultURL = url;
  normalizers.some((normalizeFunction) => {
    const newURL = normalizeFunction(url);
    if (newURL) {
      resultURL = newURL;
    }
    return !!newURL;
  });

  return resultURL;
};
