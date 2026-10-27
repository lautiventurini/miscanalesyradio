# Mis Canales

Lista de canales de TV y radio usada por el canal de Roku **Mis Canales**.

## URLs publicadas (GitHub Pages)

- JSON: `https://lautiventurini.github.io/miscanalesyradio/canales.json`
- M3U:  `https://lautiventurini.github.io/miscanalesyradio/canales.m3u`

Alternativa directa al archivo (sin CDN de Pages):

- `https://raw.githubusercontent.com/lautiventurini/miscanalesyradio/main/canales.json`

## Archivos

| Archivo | Para qué |
|---|---|
| `canales.json` | Lo lee el canal de Roku de forma remota, así se actualiza sin reinstalar la app |
| `canales.m3u` | Lista para VLC, TiviMate o cualquier reproductor IPTV |
| `cpanel/canal_luz.php` | Proxy para canalLUZ (agrega la cabecera `Referer` que el CDN exige) |

## Cómo actualizar la lista

1. Editá `canales.json` (agregá, quitá o cambiá un canal).
2. Regenerá `canales.m3u` desde el mismo JSON.
3. Commit y push.
4. El canal de Roku toma los cambios al abrirse (Pages tarda ~1 minuto en publicar).

## Formato del JSON

```json
{
  "canales": [
    { "nombre": "TVN Chile 1080p", "url": "https://...m3u8", "formato": "hls" }
  ]
}
```

`formato` acepta `hls` (TV) o `mp3` (radio).

## Advertencias

- El repo es **público** para que el Roku lo lea sin credenciales. Las URLs de los canales quedan visibles; son streams abiertos.
- **No subas acá la URL del proxy local** ni tokens: expondrías tu IP interna o credenciales.
- canalLUZ no está en el M3U ni en el JSON: necesita el proxy de `cpanel/`, porque su CDN exige un `Referer` que Roku y TiviMate no pueden mandar.
