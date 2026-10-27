# Mis Canales

Lista de canales de TV y radio usada por el canal de Roku **Mis Canales**.

## Archivos

| Archivo | Para qué |
|---|---|
| `canales.json` | Lo lee el canal de Roku de forma remota, así se actualiza sin reinstalar la app |
| `canales.m3u` | Lista para VLC, TiviMate o cualquier reproductor IPTV |

## URLs (una vez publicado con GitHub Pages)

- JSON: `https://TUUSUARIO.github.io/miscanales/canales.json`
- M3U:  `https://TUUSUARIO.github.io/miscanales/canales.m3u`

Reemplazá `TUUSUARIO` por tu nombre de usuario y `miscanales` por el nombre del repo.

## Cómo actualizar la lista

1. Editá `canales.json` (agregá, quitá o cambiá un canal).
2. Regenerá `canales.m3u` desde el mismo JSON.
3. Commit y push.
4. El canal de Roku toma los cambios al abrirse. GitHub cachea unos minutos: si no ves el cambio, esperá 5 y reabrí la app.

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

- El repo tiene que ser **público** para que el Roku lo lea sin credenciales.
- Eso significa que las URLs de los canales quedan públicas. Son streams abiertos, pero tenelo presente.
- **No subas acá la URL del proxy local** ni tokens: expondrías tu IP interna o credenciales.
- canalLUZ no está en el M3U: necesita un proxy que agregue la cabecera `Referer`, y eso no se puede hacer con GitHub.
