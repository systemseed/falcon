export const frontendURL = process.env.FRONTEND_URL;
export const backendURL = process.env.BACKEND_URL;
export const testUserPassword = process.env.TEST_USER_PASSWORD || 'password';
export const httpAuth = {
  username: process.env.HTTP_AUTH_USER,
  password: process.env.HTTP_AUTH_PASS
};

export const beforeEach = async (t) => {
  const url = await t.eval(() => window.location.href);
  console.log(`URL: ${url}`);
};
