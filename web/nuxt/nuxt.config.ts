import svgLoader from 'vite-svg-loader';

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    css: ['@/assets/styles/style.scss'],
    vite: {
        plugins: [svgLoader()]
    },
    runtimeConfig: {
        public: {
            apiBase: 'api.genious-store.com'
        }
    },
    build: {
        transpile: ['primevue']
    },
    app: {
        head: {
            meta: [
                {
                    name: 'msapplication-TileColor',
                    content: '#da532c'
                },
                {
                    name: 'theme-color',
                    content: '#ffffff'
                }
            ],
            link: [
                {
                    rel: 'apple-touch-icon',
                    sizes: '180x180',
                    href: '/apple-touch-icon.png'
                },
                {
                    rel: 'icon',
                    type: 'image/png',
                    sizes: '32x32',
                    href: '/favicon-32x32.png'
                },
                {
                    rel: 'icon',
                    type: 'image/png',
                    sizes: '16x16',
                    href: '/favicon-16x16.png'
                },
                {
                    rel: 'mask-icon',
                    color: '#5bbad5',
                    href: '/safari-pinned-tab.svg'
                }
            ]
        }
    }
});
