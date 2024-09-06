// useFetchApi(url, opts)
// Обертка над useFetch с возможностью отправлять объекты в query и базовым url API
//---------------------------
import isHTTPS from 'is-https';

// Stringify объекта в url
const stringify = (obj: any, prefix?: string): string => {
    const pairs = [];
    for (const key in obj) {
        if (!Object.prototype.hasOwnProperty.call(obj, key)) {
            continue;
        }
        const value = obj[key];
        const enkey = encodeURIComponent(key);
        let pair;
        if (typeof value === 'object') {
            pair = stringify(value, prefix ? `${prefix}[${enkey}]` : enkey);
        } else {
            pair = `${prefix ? `${prefix}[${enkey}]` : enkey}=${encodeURIComponent(value)}`;
        }
        pairs.push(pair);
    }
    return pairs.join('&');
};

type UseFetchParameters = Parameters<typeof useFetch>;

// Обертка для работы с api, дополнительно делает stringify для параметров
export default async function (request: UseFetchParameters[0], opts?: UseFetchParameters[1]) {
    if (!opts) {
        opts = {};
    }

    if (!opts?.headers) {
        opts.headers = {};
    }

    // nuxt.config.ts
    const config = useRuntimeConfig();
    let protocol = 'https:';
    if (process.server && !isHTTPS(useRequestEvent().node.req)) {
        protocol = 'http:';
    } else if (!process.server) {
        protocol = location.protocol;
    }

    // Отправляем запрос
    return await useFetch(request, {
        baseURL: protocol + '//' + config.public.apiBase,
        credentials: 'include', // Разрешаем прокидывание данных
        onRequest(context) {
            if (opts?.method && opts.method == 'POST') {
                context.options.headers = new Headers(context.options.headers);
                context.options.headers.set('Content-Type', 'application/x-www-form-urlencoded');
                context.options.body = stringify(context?.options?.body);
            } else {
                let paramsConverted = new URLSearchParams(stringify(context?.options?.params));
                context.options.params = Object.fromEntries(paramsConverted);
            }
        },
        ...opts
    });
}
