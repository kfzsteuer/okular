# OKULAR – Live-Weltkarte

Eine eigene Lagekarte im Stil von Argos Atlas. Alle Daten kommen live aus öffentlichen, kostenlosen Quellen, ganz ohne API-Schlüssel.

## Dateien

| Datei | Wofür |
|---|---|
| `index.html` | Die komplette Seite (Karte, Ebenen, Suche, Detailkarten) |
| `proxy.php` | Kleiner Proxy, damit die Flugdaten im Browser ankommen |
| `LIESMICH.md` | Diese Anleitung |

## Online stellen (empfohlen)

**Live-Version:** https://kfzsteuer.github.io/okular/ (GitHub Pages, ohne Flüge, weil dort kein PHP läuft).

**Auf eigenem Webspace mit Flügen:**
1. `index.html` und `proxy.php` per FTP/SFTP in **denselben Ordner** auf einen Webspace mit PHP laden (PHP 7.4+ mit cURL, gibt es bei praktisch jedem Hoster), z. B. `https://deine-domain.de/okular/`.
2. In `index.html` oben bei `CFG` die Zeile `proxy: ''` in `proxy: 'proxy.php'` ändern.
3. Die Adresse im Browser öffnen. Fertig.

Ohne Proxy läuft alles außer den beiden Flug-Ebenen. Diese zeigen dann „Flug-Proxy nicht eingerichtet“.

**Nur lokal testen:** `index.html` per Doppelklick öffnen geht auch, dann ohne Flüge, und die Ortssuche kann zickig sein. Mit PHP auf dem Rechner geht es vollständig:
```
cd ordner-mit-den-dateien
php -S localhost:8000
```
und dann `http://localhost:8000` öffnen.

## Datenquellen

| Ebene | Quelle | Abdeckung |
|---|---|---|
| Webcams | Digitraffic (Finnische Verkehrsbehörde), Caltrans | ca. 3.000 Kameras: Finnland und Kalifornien, Standbilder |
| Flüge im Ausschnitt | adsb.lol (Ausweichquelle airplanes.live), über `proxy.php` | weltweit, ab Zoomstufe 6, Radius bis 250 Seemeilen |
| Militärflüge | adsb.lol `/v2/mil`, über `proxy.php` | weltweit, nur Flugzeuge mit eingeschaltetem Transponder |
| Schiffe | Digitraffic AIS | Ostsee, vor allem Finnischer Meerbusen und Bottnischer Meerbusen |
| Erdbeben | USGS | weltweit, ab Magnitude 2,5, letzte 24 h |
| Naturereignisse | NASA EONET | Brände, Stürme, Vulkane, Eisberge |
| Katastrophenwarnungen | GDACS (UN/EU) | Warnstufe Orange und Rot, letzte 14 Tage |
| Internet-Störungen | IODA (Georgia Tech) | Länder mit aktuellem Einbruch |
| Satelliten & ISS | CelesTrak, Position mit satellite.js berechnet | ca. 160 helle Satelliten und Raumstationen, ISS mit Bahn |
| Wetterradar | RainViewer | weltweit, letztes Radarbild |
| Märkte | CoinGecko | Bitcoin, Ethereum, Gold (PAXG) |
| Karte | Esri | Nacht, Satellit, Hell |
| Ortssuche | OpenStreetMap Nominatim | weltweit |

## Anpassen

Ganz oben im Skript von `index.html` steht `CFG`:
- `proxy`: Pfad oder Adresse des Flug-Proxys, z. B. `'proxy.php'` oder die Adresse eines Cloudflare Workers. Leer = keine Flüge.
- `caltrans`: welche Kalifornien-Bezirke geladen werden (`d1` bis `d12`). Mehr Bezirke heißt mehr Kameras und längere Ladezeit.
- `flightZoom`: ab welcher Zoomstufe alle Flüge geladen werden.

Meerengen und Texte stehen in der Liste `CHOKE` und lassen sich dort ergänzen.

## Gut zu wissen

- `proxy.php` leitet **nur** an die zwei Flugdienste weiter und puffert jede Antwort 10 Sekunden. Es ist also kein offener Proxy.
- Die freien Dienste haben keine Garantie. Fällt einer aus, zeigt die Ebene „Quelle nicht erreichbar“, der Rest läuft weiter.
- Merkliste, aktive Ebenen und der letzte Kartenausschnitt bleiben im Browser gespeichert.
- Bedienung: Klick auf einen Punkt öffnet die Details. Rechtsklick (mobil: lange drücken) zeigt „Was ist hier?“. Esc schließt die Detailkarte.
