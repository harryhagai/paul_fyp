# Mwongozo wa Mfumo wa E-Commerce na Robot Arm

## 1. Utangulizi

Mfumo huu unaunganisha website ya e-commerce na ESP32 inayosimamia robot arm. Website haitumi maagizo ya moja kwa moja ya motor, servo angle, au step count. Badala yake, website inatuma amri ya kiwango cha juu inayoeleza kazi inayotakiwa kufanyika.

Mfano wa amri:

```json
{
  "command": "PICK",
  "order_id": "ORD1001",
  "location": 3
}
```

Amri hii inamaanisha robot ichukue parcel ya order `ORD1001` iliyopo `LOCATION 3`, ipeleke kwenye conveyor, kisha irudi `HOME`.

## 2. Mfumo Unavyofanya Kazi

Mawasiliano yanafuata mtiririko huu:

```text
Customer
   ↓
E-Commerce Website
   ↓ FIFO Robot Command Queue
Queue Worker
   ↓ HTTP POST kupitia Wi-Fi
ESP32
   ↓
Robot Arm
   ↓
Conveyor
```

Website na ESP32 lazima ziwe kwenye mtandao unaowezesha server kufikia IP address ya ESP32.

Mtiririko kamili ni:

1. Seller anaweka `robot_location` kwenye product.
2. Customer anaweka order na kukamilisha malipo.
3. Order ikithibitishwa, website inatafuta robot location ya product.
4. Website inagawa order kuwa pick cycle moja kwa kila physical item.
5. Pick cycles zinahifadhiwa kwenye FIFO queue kwa status `QUEUED`.
6. Queue worker inatuma oldest queued cycle ikiwa hakuna active command.
7. ESP32 inakagua command, order ID, na location.
8. ESP32 inatumia trajectory iliyofundishwa kuchukua parcel.
9. Robot inaweka parcel kwenye conveyor na kurudi `HOME`.
10. Website inauliza status ya robot kila baada ya sekunde tano.
11. ESP32 ikirudisha `COMPLETED`, worker inatuma cycle inayofuata.
12. Order inawekwa completed baada ya pick cycles zake zote kukamilika.

## 3. Sehemu Muhimu za Mfumo

### Product Robot Location

Kila product inayoweza kuchukuliwa na robot inapaswa kupewa location kwenye seller products page.

Locations za mwanzo ni:

- `LOCATION 1`
- `LOCATION 2`
- `LOCATION 3`
- `LOCATION 4`
- `LOCATION 5`

Idadi hii inaweza kubadilishwa kupitia `ROBOT_ARM_LOCATION_COUNT`.

Product isipopewa location, website haitatuma `PICK`. Command itahifadhiwa ikiwa na error `LOCATION_NOT_ASSIGNED`.

### Robot Command History

Kila command inahifadhi taarifa zifuatazo:

- Command iliyotumwa
- Order reference
- Robot location
- Queue batch na cycle number
- Request JSON
- Response JSON
- Status
- Error
- Muda wa kutumwa
- Muda wa kukamilika au kushindwa

Hii inasaidia kufuatilia matatizo na kuona historia ya robot.

### Seller Robot Arm Monitor

Seller anaweza kufungua:

```text
/seller/robot-arm
```

Ukurasa huu unaonyesha:

- Kama ESP32 imeunganishwa
- Status ya sasa ya robot
- Active command
- Oldest queued command na cycle progress
- Order inayoshughulikiwa
- Parcel location
- Error ya mwisho
- Command history

Seller pia anaweza kutumia:

- `PICK` — kuchukua parcel ya order iliyochaguliwa
- `HOME` — kurudisha robot kwenye home position
- `STOP` — kusimamisha robot
- `Refresh` — kuomba status mpya

Manual `PICK` inaruhusiwa kwa order yenye status `confirmed` pekee.

## 4. Commands Zinazotumika

### PICK

Inachukua parcel kutoka location iliyotumwa na kuiweka kwenye conveyor.

```json
{
  "command": "PICK",
  "order_id": "ORD1001",
  "location": 3
}
```

### HOME

Inarudisha robot kwenye home position.

```json
{
  "command": "HOME"
}
```

### STOP

Inasimamisha operation ya robot.

```json
{
  "command": "STOP"
}
```

### STATUS

Inaomba status ya sasa ya robot.

```json
{
  "command": "STATUS"
}
```

Commands zote zinatumwa kwa:

```text
POST /robot/command
Content-Type: application/json
Accept: application/json
```

## 5. Robot Status

ESP32 inapaswa kutumia status zifuatazo:

| Status | Maana |
| --- | --- |
| `IDLE` | Robot haina kazi inayoendelea |
| `ACCEPTED` | Command imepokelewa na kukubaliwa |
| `MOVING` | Robot inasogea kuelekea position |
| `PICKING` | Robot inachukua parcel |
| `PLACING` | Robot inaweka parcel kwenye conveyor |
| `COMPLETED` | Operation imekamilika |
| `ERROR` | Operation imeshindwa |
| `STOPPED` | Robot imesimamishwa |

Mfano wa response iliyokubaliwa:

```json
{
  "status": "ACCEPTED",
  "order_id": "ORD1001",
  "location": 3
}
```

Mfano wa operation iliyokamilika:

```json
{
  "status": "COMPLETED",
  "order_id": "ORD1001",
  "location": 3
}
```

Website hairuhusu response ya zamani kurudisha status nyuma. Kwa mfano, command ikiwa tayari `PLACING`, response iliyochelewa yenye `MOVING` haitabadilisha status kuwa `MOVING`.

## 6. Error Handling

ESP32 inaweza kurudisha error kama:

```json
{
  "status": "ERROR",
  "order_id": "ORD1001",
  "error": "ROBOT_BUSY"
}
```

Errors zinazotarajiwa kutoka ESP32 ni:

- `INVALID_COMMAND`
- `INVALID_LOCATION`
- `ROBOT_BUSY`
- `EMERGENCY_STOP`
- `MOVEMENT_ERROR`
- `PICK_FAILED`

Website pia inaweza kuhifadhi errors zifuatazo:

- `ROBOT_NOT_CONFIGURED` — robot haijawezeshwa au URL haijawekwa
- `CONNECTION_FAILED` — website imeshindwa kuifikia ESP32
- `INVALID_ROBOT_RESPONSE` — ESP32 imerudisha JSON au status isiyokubalika
- `LOCATION_NOT_ASSIGNED` — product haina robot location
- `PREVIOUS_PICK_FAILED` — cycle haikutumwa kwa sababu cycle ya awali ya batch imeshindwa
- `ROBOT_STOPPED` — PICK operation imesimamishwa
- `COMMAND_FAILED` — command imeshindwa kwa sababu nyingine

Connection au status poll ikishindwa kwa muda, active command haibadilishwi moja kwa moja kuwa failed. Hii inalinda command dhidi ya network interruption ya muda mfupi.

## 7. Masharti ya Order

Automatic `PICK` inafanyika baada ya order kuthibitishwa.

Kwa automatic queue:

- Order inaweza kuwa na products nyingi.
- Quantity inaweza kuwa zaidi ya `1`; kila unit inapata pick cycle yake.
- Kila product lazima iwe na robot location.
- Location lazima iwe ndani ya locations zilizowekwa kwenye configuration.

Mfano: order yenye Product A quantity `2` na Product B quantity `1` inatengeneza cycles tatu. Cycles zinafanyika kwa sequence `1/3`, `2/3`, na `3/3`. Order haibadiliki kuwa completed mpaka cycles zote tatu zirudishe `COMPLETED`.

Orders nyingi zinafuata FIFO kwa command ID: order iliyoingia queue kwanza inashughulikiwa kwanza. Website haitumi `PICK` ya pili wakati command ya kwanza bado iko active.

## 8. Usanidi wa Website

Fungua `.env` na uweke:

```env
ROBOT_ARM_ENABLED=true
ROBOT_ARM_BASE_URL=http://192.168.1.50
ROBOT_ARM_COMMAND_ENDPOINT=/robot/command
ROBOT_ARM_TIMEOUT=5
ROBOT_ARM_LOCATION_COUNT=5
```

Maana ya settings:

| Setting | Maana |
| --- | --- |
| `ROBOT_ARM_ENABLED` | Inawasha au kuzima robot integration |
| `ROBOT_ARM_BASE_URL` | IP address au base URL ya ESP32 |
| `ROBOT_ARM_COMMAND_ENDPOINT` | Endpoint inayopokea commands |
| `ROBOT_ARM_TIMEOUT` | Sekunde za kusubiri HTTP response |
| `ROBOT_ARM_LOCATION_COUNT` | Idadi ya parcel locations zilizofundishwa |

Baada ya kubadilisha `.env`, tumia:

```bash
php artisan config:clear
php artisan migrate
```

Kwa development, anzisha mfumo kwa:

```bash
composer run dev
```

Command hii inaanzisha:

- Laravel development server
- Queue worker
- Robot status scheduler
- Vite frontend server

Production server inapaswa kuwa na Laravel scheduler inayofanya kazi muda wote.

## 9. Background Queue Processing na Status Polling

Website ina command ya kuendesha queue worker na kuomba status ya active command:

```bash
php artisan robot:poll
```

Scheduler huiendesha kila baada ya sekunde tano. Ikiwa kuna active command, ina-poll status yake. Ikiwa imekamilika au hakuna active command, inatuma oldest `QUEUED` cycle. Ikiwa queue ni empty, hakuna HTTP request inayotumwa kwa ESP32.

Kwa production, hakikisha scheduler imeanzishwa kwa mojawapo ya njia zinazokubalika na mazingira ya server. Mfano wa process ya muda wote:

```bash
php artisan schedule:work
```

Scheduler ni muhimu kwa sababu order inaweza kukamilishwa hata kama seller hajafungua Robot Arm Monitor.

## 10. Kuandaa ESP32

ESP32 developer anapaswa kuhakikisha:

1. ESP32 imeunganishwa kwenye Wi-Fi inayofikiwa na website server.
2. ESP32 ina HTTP server.
3. Endpoint `POST /robot/command` inapatikana.
4. Request body inasomwa kama JSON.
5. Commands na locations zinakaguliwa kabla ya robot kusogea.
6. Robot hairuhusu `PICK` mpya ikiwa tayari ina operation.
7. Trajectories za `HOME`, `LOCATION 1–5`, na `CONVEYOR` zimefundishwa.
8. Responses zote zinarudishwa kama JSON yenye `status`.
9. Responses za operation zinajumuisha `order_id` sahihi.
10. Emergency stop inafanya kazi bila kutegemea website.

## 11. Jinsi ya Kupima Bila Robot Halisi

Unaweza kutumia API mock server, Postman mock, au server ndogo inayopokea:

```text
POST /robot/command
```

Mock response ya kwanza inaweza kuwa:

```json
{
  "status": "ACCEPTED",
  "order_id": "ORD1001",
  "location": 3
}
```

Baadaye, request ya `STATUS` irudishe:

```json
{
  "status": "COMPLETED",
  "order_id": "ORD1001",
  "location": 3
}
```

Automated tests zinaweza kuendeshwa kwa:

```bash
php artisan test --filter=RobotArmIntegrationTest
```

Au tests zote:

```bash
php artisan test
```

## 12. Troubleshooting

### Robot inaonyesha “Not configured”

Hakikisha:

```env
ROBOT_ARM_ENABLED=true
ROBOT_ARM_BASE_URL=http://IP-YA-ESP32
```

Kisha tumia:

```bash
php artisan config:clear
```

### CONNECTION_FAILED

Kagua:

- ESP32 imewashwa.
- Website server na ESP32 zinaweza kuwasiliana kwenye network.
- IP address ya ESP32 haijabadilika.
- Port iliyowekwa ni sahihi.
- Firewall haizuii connection.
- Endpoint `/robot/command` ipo.

Jaribu kutoka kwenye computer ya website:

```bash
curl -X POST http://IP-YA-ESP32/robot/command \
  -H "Content-Type: application/json" \
  -d '{"command":"STATUS"}'
```

### INVALID_ROBOT_RESPONSE

Hakikisha ESP32 inarudisha valid JSON na status inayokubalika. Response isiwe plain text au HTML.

Sahihi:

```json
{
  "status": "IDLE"
}
```

Isiyo sahihi:

```text
Robot is idle
```

### LOCATION_NOT_ASSIGNED

Fungua seller products page, edit product, kisha chagua Robot Location.

### Command inabaki QUEUED

Hii ni status ya kawaida ikiwa robot inashughulikia command nyingine. Hakikisha Laravel scheduler inaendelea kufanya kazi. `ROBOT_BUSY` ikirudishwa, cycle inarudishwa kwenye queue na itajaribiwa tena.

### Order haibadiliki kuwa completed

Kagua:

- Order ilikuwa `confirmed` kabla ya robot kukamilisha.
- ESP32 imerudisha `COMPLETED` pamoja na `order_id` sahihi.
- Laravel scheduler inaendelea kufanya kazi.
- Robot command history haina error.

Jaribu:

```bash
php artisan robot:poll
```

## 13. Checklist ya Demo

Kabla ya demonstration:

- [ ] Website na ESP32 zipo kwenye network inayoruhusu mawasiliano.
- [ ] ESP32 ina IP address isiyobadilika wakati wa demo.
- [ ] `.env` ina ESP32 URL sahihi.
- [ ] Migrations zimeendeshwa.
- [ ] Scheduler inaendelea kufanya kazi.
- [ ] Products za demo zimepewa locations 1–5.
- [ ] Robot positions zimefundishwa na kuhifadhiwa.
- [ ] `HOME` na emergency `STOP` zimepimwa.
- [ ] `STATUS` inarudisha valid JSON.
- [ ] Test order ya location zote tano imejaribiwa.
- [ ] Seller anaweza kuona command history na errors.

## 14. Muhtasari

Website inagawa confirmed orders kuwa pick cycles, inazihifadhi kwenye persistent FIFO queue, na kutuma high-level `PICK` command moja kwa wakati kwa ESP32 kupitia HTTP. ESP32 ndiyo inayosimamia robot movement kwa kutumia trajectories zilizofundishwa. Website inafuatilia kila cycle, inatuma inayofuata baada ya `COMPLETED`, na inabadilisha order kuwa completed baada ya cycles zake zote kukamilika.
