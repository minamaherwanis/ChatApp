import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'
const isSecure = reverbScheme === 'https'

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT),
    forceTLS: isSecure,
    disableStats: true,
    enabledTransports: isSecure ? ['wss'] : ['ws'],
});
