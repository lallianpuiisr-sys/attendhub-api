import axios, { type AxiosInstance, type AxiosRequestConfig } from "axios";

export type ApiClientOptions = {
  baseURL?: string;
  withCredentials?: boolean;
  csrfCookieUrl?: string;
  getAuthToken?: () => string | null;
};

const DEFAULT_TOKEN_KEY = "auth_token";

const isBrowser = () => typeof window !== "undefined";

const defaultGetAuthToken = () => {
  if (!isBrowser()) return null;
  try {
    return window.localStorage.getItem(DEFAULT_TOKEN_KEY);
  } catch {
    return null;
  }
};

const createCsrfClient = (baseURL?: string) =>
  axios.create({
    baseURL,
    withCredentials: true,
  });

const shouldAttachCsrf = (config: AxiosRequestConfig) => {
  const method = (config.method || "get").toLowerCase();
  return method !== "get" && method !== "head" && method !== "options";
};

export const createApiClient = (options: ApiClientOptions = {}) => {
  const {
    baseURL = isBrowser() ? (import.meta as any)?.env?.VITE_API_URL ?? "" : "",
    withCredentials = true,
    csrfCookieUrl = "/sanctum/csrf-cookie",
    getAuthToken = defaultGetAuthToken,
  } = options;

  const client = axios.create({
    baseURL,
    withCredentials,
    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  });

  const csrfClient = createCsrfClient(baseURL);
  let csrfPromise: Promise<void> | null = null;

  const ensureCsrfCookie = async () => {
    if (!csrfCookieUrl) return;
    if (!csrfPromise) {
      csrfPromise = csrfClient
        .get(csrfCookieUrl)
        .then(() => undefined)
        .finally(() => {
          csrfPromise = null;
        });
    }
    await csrfPromise;
  };

  client.interceptors.request.use(async (config) => {
    if (shouldAttachCsrf(config)) {
      await ensureCsrfCookie();
    }

    const token = getAuthToken();
    if (token) {
      config.headers = {
        ...(config.headers || {}),
        Authorization: `Bearer ${token}`,
      };
    }

    return config;
  });

  return {
    client,
    ensureCsrfCookie,
    setAuthToken: (token: string | null) => {
      if (!isBrowser()) return;
      try {
        if (token) {
          window.localStorage.setItem(DEFAULT_TOKEN_KEY, token);
        } else {
          window.localStorage.removeItem(DEFAULT_TOKEN_KEY);
        }
      } catch {
        // ignore storage errors
      }
    },
    clearAuthToken: () => {
      if (!isBrowser()) return;
      try {
        window.localStorage.removeItem(DEFAULT_TOKEN_KEY);
      } catch {
        // ignore storage errors
      }
    },
  };
};

const { client, ensureCsrfCookie, setAuthToken, clearAuthToken } = createApiClient();

export { client as apiClient, ensureCsrfCookie, setAuthToken, clearAuthToken };
