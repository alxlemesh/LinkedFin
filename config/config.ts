const readEnv = (envName: any) => {
  const value = process.env[envName];
  if (!value) throw new Error(`Missing env variable: ${envName}`);
  return value;
};

const config = {
  privateKey: readEnv('PRIVATE_KEY'),
} as const;

export default config;
