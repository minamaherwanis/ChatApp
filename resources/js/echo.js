import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'
const isSecure = reverbScheme === 'https'
const configuredHost = import.meta.env.VITE_REVERB_HOST
const browserHost = window.location.hostname
const localHosts = new Set(['localhost', '127.0.0.1'])

const wsHost = localHosts.has(browserHost)
    ? browserHost
    : (configuredHost ?? browserHost)
const wsPort = Number(import.meta.env.VITE_REVERB_PORT ?? (isSecure ? 443 : 80))

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost,
    wsPort,
    wssPort: wsPort,
    forceTLS: isSecure,
    disableStats: true,
    enabledTransports: isSecure ? ['wss'] : ['ws'],
});
