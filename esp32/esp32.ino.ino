#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP32Servo.h>

// =====================================================
// PINES
// =====================================================

// RC522
#define SS_PIN  21
#define RST_PIN 22

// Sensor 1: antes de la barrera
#define TRIG_PIN 13
#define ECHO_PIN 12  // Usar divisor de voltaje

// Sensor 2: después de la barrera
#define TRIG2_PIN 14
#define ECHO2_PIN 27 // Usar divisor de voltaje

// Servomotor
#define SERVO_PIN 26

// =====================================================
// OBJETOS
// =====================================================

MFRC522 rfid(SS_PIN, RST_PIN);
Servo servoBar;

// =====================================================
// RED
// =====================================================

const char* ssid = "iPhone de Kevin";
const char* password = "kevin123";

// Servidor XAMPP
const char* serverName =
  "http://172.20.10.14/validate_access.php";

// ESP32-CAM
const char* camServer =
  "http://172.20.10.4/capture";

// Archivo PHP que recibe la imagen
const char* uploadServer =
  "http://172.20.10.14/upload_image.php";

// Consultar aperturas manuales
const char* manualCommandServer =
  "http://172.20.10.14/check_manual_open.php";

// Debe coincidir con check_manual_open.php
const char* manualCommandToken =
  "ANDE_BARRERA_2026_SEGURA";

// =====================================================
// PARÁMETROS
// =====================================================

const int CLOSED_ANGLE = 0;
const int OPEN_ANGLE = 90;

// Tiempo máximo para presentar RFID
const unsigned long RFID_TIMEOUT_MS = 15000;

// Distancia del sensor 1
const int VEHICLE_DISTANCE_CM = 3;

// Distancia del sensor 2
const int CLEAR_DISTANCE_CM = 5;

// Tiempo libre antes de cerrar
const unsigned long CLEAR_CONFIRM_MS = 2000;

// Intervalo entre mediciones
const unsigned long MEASURE_INTERVAL_MS = 150;

// Consultar órdenes manuales cada 1,5 segundos
const unsigned long MANUAL_CHECK_INTERVAL_MS = 1500;

// Intentar reconectar WiFi cada 5 segundos
const unsigned long WIFI_RECONNECT_INTERVAL_MS = 5000;

// =====================================================
// ESTADOS
// =====================================================

enum State {
  IDLE,
  WAITING_RFID,
  BARRIER_OPEN,
  WAIT_VEHICLE_CLEAR
};

State state = IDLE;

// =====================================================
// VARIABLES
// =====================================================

unsigned long waitingStart = 0;
unsigned long lastMeasureTime = 0;
unsigned long clearSince = 0;

unsigned long lastManualCheckTime = 0;
unsigned long lastWiFiReconnectTime = 0;

bool vehicleCrossedSecondSensor = false;
bool manualOpening = false;

int currentManualCommandId = 0;

// =====================================================
// PROTOTIPOS
// =====================================================

String readUIDString();

String queryServer(
  const String &uid
);

String checkManualCommand();

bool captureManualPhoto(
  int commandId
);

String urlEncode(
  const String &texto
);

int measureDistanceCM(
  int trigPin,
  int echoPin
);

void openBarrier();

void closeBarrier();

void startBarrierOpening(
  bool isManual,
  int commandId
);

void reconnectWiFi();

// =====================================================
// SETUP
// =====================================================

void setup() {
  Serial.begin(115200);

  // RFID
  SPI.begin();
  rfid.PCD_Init();

  // Sensor 1
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  // Sensor 2
  pinMode(TRIG2_PIN, OUTPUT);
  pinMode(ECHO2_PIN, INPUT);

  digitalWrite(TRIG_PIN, LOW);
  digitalWrite(TRIG2_PIN, LOW);

  // Servo
  servoBar.attach(SERVO_PIN);
  servoBar.write(CLOSED_ANGLE);

  // WiFi
  Serial.print("Conectando a WiFi");

  WiFi.begin(
    ssid,
    password
  );

  while (
    WiFi.status() != WL_CONNECTED
  ) {
    delay(300);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("WiFi conectado.");

  Serial.print("IP ESP32 principal: ");
  Serial.println(WiFi.localIP());

  Serial.println("RFID inicializado.");
  Serial.println("Sistema listo.");
}

// =====================================================
// LOOP
// =====================================================

void loop() {
  unsigned long now = millis();

  reconnectWiFi();

  // ===================================================
  // CONSULTAR APERTURA MANUAL
  //
  // Se permite cuando:
  // - El sistema está libre.
  // - Está esperando RFID.
  // - El acceso fue denegado y el vehículo sigue ahí.
  //
  // No se consulta si la barrera ya está abierta.
  // ===================================================

  bool puedeRecibirOrdenManual =
    state == IDLE ||
    state == WAITING_RFID ||
    state == WAIT_VEHICLE_CLEAR;

  if (
    puedeRecibirOrdenManual &&
    WiFi.status() == WL_CONNECTED &&
    now - lastManualCheckTime >=
      MANUAL_CHECK_INTERVAL_MS
  ) {
    lastManualCheckTime = now;

    String manualResponse =
      checkManualCommand();

    if (
      manualResponse.startsWith("OPEN:")
    ) {
      String idTexto =
        manualResponse.substring(5);

      idTexto.trim();

      int commandId =
        idTexto.toInt();

      if (commandId > 0) {
        Serial.print(
          "Orden manual recibida. ID: "
        );

        Serial.println(commandId);

        Serial.println(
          "Solicitando fotografía manual..."
        );

        bool fotoOk =
          captureManualPhoto(commandId);

        if (fotoOk) {
          Serial.println(
            "Fotografía manual enviada correctamente."
          );
        } else {
          Serial.println(
            "No se pudo obtener la fotografía manual."
          );
        }

        startBarrierOpening(
          true,
          commandId
        );

        return;
      }
    }
  }

  // Ejecutar máquina de estados cada 150 ms
  if (
    now - lastMeasureTime <
    MEASURE_INTERVAL_MS
  ) {
    return;
  }

  lastMeasureTime = now;

  switch (state) {

    // =================================================
    // ESPERAR VEHÍCULO
    // =================================================

    case IDLE: {
      int dist1 = measureDistanceCM(
        TRIG_PIN,
        ECHO_PIN
      );

      if (
        dist1 > 0 &&
        dist1 <= VEHICLE_DISTANCE_CM
      ) {
        Serial.print(
          "Vehículo detectado por sensor 1 a "
        );

        Serial.print(dist1);

        Serial.println(
          " cm. Esperando RFID."
        );

        state = WAITING_RFID;
        waitingStart = now;
      }

      break;
    }

    // =================================================
    // ESPERAR Y VALIDAR RFID
    // =================================================

    case WAITING_RFID: {

      if (
        now - waitingStart >=
        RFID_TIMEOUT_MS
      ) {
        Serial.println(
          "Tiempo de espera RFID finalizado."
        );

        Serial.println(
          "Esperando que el vehículo se retire."
        );

        state = WAIT_VEHICLE_CLEAR;

        break;
      }

      if (
        rfid.PICC_IsNewCardPresent() &&
        rfid.PICC_ReadCardSerial()
      ) {
        String uid =
          readUIDString();

        Serial.print(
          "UID detectado: "
        );

        Serial.println(uid);

        String response =
          queryServer(uid);

        Serial.print(
          "Respuesta del servidor: "
        );

        Serial.println(response);

        if (
          response == "ACCESS_GRANTED"
        ) {
          Serial.println(
            "Acceso automático autorizado."
          );

          startBarrierOpening(
            false,
            0
          );
        }
        else if (
          response == "ACCESS_DENIED"
        ) {
          Serial.println(
            "Acceso denegado."
          );

          Serial.println(
            "Puede autorizarse manualmente desde el dashboard."
          );

          Serial.println(
            "Esperando retiro del vehículo o apertura manual."
          );

          state = WAIT_VEHICLE_CLEAR;
        }
        else {
          Serial.print(
            "No se pudo confirmar el acceso. Respuesta: "
          );

          Serial.println(response);

          Serial.println(
            "Puede autorizarse manualmente desde el dashboard."
          );

          Serial.println(
            "Esperando retiro del vehículo o apertura manual."
          );

          state = WAIT_VEHICLE_CLEAR;
        }

        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
      }

      break;
    }

    // =================================================
    // ACCESO DENEGADO
    // ESPERAR RETIRO O AUTORIZACIÓN MANUAL
    // =================================================

    case WAIT_VEHICLE_CLEAR: {
      int dist1 = measureDistanceCM(
        TRIG_PIN,
        ECHO_PIN
      );

      if (
        dist1 == -1 ||
        dist1 > VEHICLE_DISTANCE_CM
      ) {
        Serial.println(
          "Sensor 1 libre."
        );

        Serial.println(
          "Sistema listo para otro vehículo."
        );

        state = IDLE;
      }

      break;
    }

    // =================================================
    // BARRERA ABIERTA
    // =================================================

    case BARRIER_OPEN: {
      int dist2 = measureDistanceCM(
        TRIG2_PIN,
        ECHO2_PIN
      );

      // Vehículo presente frente al sensor 2
      if (
        dist2 > 0 &&
        dist2 <= CLEAR_DISTANCE_CM
      ) {
        if (
          !vehicleCrossedSecondSensor
        ) {
          Serial.print(
            "Vehículo detectado por sensor 2 a "
          );

          Serial.print(dist2);
          Serial.println(" cm.");
        }

        vehicleCrossedSecondSensor = true;
        clearSince = 0;
      }

      // Solo cerrar después de detectar el vehículo
      else if (
        vehicleCrossedSecondSensor
      ) {
        if (clearSince == 0) {
          clearSince = now;

          if (dist2 == -1) {
            Serial.println(
              "Sensor 2 libre sin eco. Confirmando salida."
            );
          } else {
            Serial.println(
              "Sensor 2 libre. Confirmando salida."
            );
          }
        }

        if (
          now - clearSince >=
          CLEAR_CONFIRM_MS
        ) {
          Serial.println(
            "Vehículo cruzó. Cerrando barrera."
          );

          closeBarrier();

          state = IDLE;

          vehicleCrossedSecondSensor = false;
          clearSince = 0;

          manualOpening = false;
          currentManualCommandId = 0;

          Serial.println(
            "Sistema listo."
          );
        }
      }

      break;
    }
  }
}

// =====================================================
// INICIAR APERTURA
// =====================================================

void startBarrierOpening(
  bool isManual,
  int commandId
) {
  manualOpening = isManual;
  currentManualCommandId = commandId;

  if (manualOpening) {
    Serial.print(
      "Apertura manual autorizada. Orden ID: "
    );

    Serial.println(
      currentManualCommandId
    );
  } else {
    Serial.println(
      "Apertura automática autorizada."
    );
  }

  openBarrier();

  vehicleCrossedSecondSensor = false;
  clearSince = 0;

  state = BARRIER_OPEN;

  Serial.println(
    "Barrera abierta."
  );

  Serial.println(
    "Esperando cruce por sensor 2."
  );
}

// =====================================================
// LEER UID
// =====================================================

String readUIDString() {
  String uid = "";

  for (
    byte i = 0;
    i < rfid.uid.size;
    i++
  ) {
    if (
      rfid.uid.uidByte[i] < 0x10
    ) {
      uid += "0";
    }

    uid += String(
      rfid.uid.uidByte[i],
      HEX
    );
  }

  uid.toUpperCase();

  return uid;
}

// =====================================================
// VALIDACIÓN AUTOMÁTICA
// =====================================================

String queryServer(
  const String &uid
) {
  if (
    WiFi.status() != WL_CONNECTED
  ) {
    Serial.println(
      "No hay conexión WiFi."
    );

    return "NETWORK_ERROR";
  }

  HTTPClient http;

  String url =
    String(serverName) +
    "?uid=" +
    uid;

  Serial.print(
    "Consultando servidor: "
  );

  Serial.println(url);

  http.setReuse(false);
  http.setConnectTimeout(10000);
  http.setTimeout(60000);

  if (!http.begin(url)) {
    Serial.println(
      "No se pudo iniciar HTTP."
    );

    return "HTTP_BEGIN_ERROR";
  }

  unsigned long inicio =
    millis();

  int code =
    http.GET();

  Serial.print(
    "Tiempo de respuesta: "
  );

  Serial.print(
    millis() - inicio
  );

  Serial.println(" ms");

  String payload = "";

  if (
    code == HTTP_CODE_OK
  ) {
    payload =
      http.getString();

    payload.trim();

    if (payload == "") {
      payload =
        "SERVER_EMPTY_RESPONSE";
    }
  } else {
    Serial.print(
      "Error HTTP: "
    );

    Serial.print(code);
    Serial.print(" - ");

    Serial.println(
      HTTPClient::errorToString(code)
    );

    payload =
      "SERVER_TIMEOUT";
  }

  http.end();

  return payload;
}

// =====================================================
// CONSULTAR ORDEN MANUAL
// =====================================================

String checkManualCommand() {
  if (
    WiFi.status() != WL_CONNECTED
  ) {
    return "NETWORK_ERROR";
  }

  HTTPClient http;

  String url =
    String(manualCommandServer) +
    "?token=" +
    manualCommandToken;

  http.setReuse(false);
  http.setConnectTimeout(5000);
  http.setTimeout(8000);

  if (!http.begin(url)) {
    Serial.println(
      "No se pudo iniciar consulta manual."
    );

    return "HTTP_BEGIN_ERROR";
  }

  int code =
    http.GET();

  String payload = "";

  if (
    code == HTTP_CODE_OK
  ) {
    payload =
      http.getString();

    payload.trim();

    if (
      payload != "NO_COMMAND"
    ) {
      Serial.print(
        "Respuesta de apertura manual: "
      );

      Serial.println(payload);
    }
  }
  else {
    Serial.print(
      "Error consultando orden manual: "
    );

    Serial.println(code);

    payload = "HTTP_ERROR";
  }

  http.end();

  return payload;
}

// =====================================================
// SOLICITAR FOTO MANUAL
// =====================================================

bool captureManualPhoto(
  int commandId
) {
  if (
    WiFi.status() != WL_CONNECTED
  ) {
    return false;
  }

  String fileName =
    "manual_command_" +
    String(commandId) +
    ".jpg";

  String url =
    String(camServer) +
    "?name=" +
    urlEncode(fileName) +
    "&server=" +
    urlEncode(
      String(uploadServer)
    );

  Serial.print(
    "Solicitando foto manual: "
  );

  Serial.println(url);

  HTTPClient http;

  http.setReuse(false);
  http.setConnectTimeout(10000);
  http.setTimeout(30000);

  if (!http.begin(url)) {
    Serial.println(
      "No se pudo iniciar solicitud a ESP32-CAM."
    );

    return false;
  }

  int code =
    http.GET();

  String respuesta = "";

  if (code > 0) {
    respuesta =
      http.getString();

    respuesta.trim();
  }

  Serial.print(
    "Respuesta ESP32-CAM: "
  );

  Serial.println(respuesta);

  http.end();

  return (
    code == HTTP_CODE_OK &&
    respuesta == "OK"
  );
}

// =====================================================
// CODIFICAR URL
// =====================================================

String urlEncode(
  const String &texto
) {
  String resultado = "";

  const char hexadecimal[] =
    "0123456789ABCDEF";

  for (
    unsigned int i = 0;
    i < texto.length();
    i++
  ) {
    unsigned char caracter =
      texto.charAt(i);

    if (
      isalnum(caracter) ||
      caracter == '-' ||
      caracter == '_' ||
      caracter == '.' ||
      caracter == '~'
    ) {
      resultado +=
        (char) caracter;
    } else {
      resultado += '%';

      resultado +=
        hexadecimal[
          (caracter >> 4) & 0x0F
        ];

      resultado +=
        hexadecimal[
          caracter & 0x0F
        ];
    }
  }

  return resultado;
}

// =====================================================
// MEDIR DISTANCIA
// =====================================================

int measureDistanceCM(
  int trigPin,
  int echoPin
) {
  digitalWrite(
    trigPin,
    LOW
  );

  delayMicroseconds(2);

  digitalWrite(
    trigPin,
    HIGH
  );

  delayMicroseconds(10);

  digitalWrite(
    trigPin,
    LOW
  );

  unsigned long duration =
    pulseIn(
      echoPin,
      HIGH,
      30000UL
    );

  if (duration == 0) {
    return -1;
  }

  return (int)(
    duration / 58.0
  );
}

// =====================================================
// ABRIR BARRERA
// =====================================================

void openBarrier() {
  servoBar.write(
    OPEN_ANGLE
  );

  delay(700);

  Serial.println(
    "Barrera abierta."
  );
}

// =====================================================
// CERRAR BARRERA
// =====================================================

void closeBarrier() {
  servoBar.write(
    CLOSED_ANGLE
  );

  delay(700);

  Serial.println(
    "Barrera cerrada."
  );
}

// =====================================================
// RECONECTAR WIFI
// =====================================================

void reconnectWiFi() {
  if (
    WiFi.status() == WL_CONNECTED
  ) {
    return;
  }

  unsigned long now =
    millis();

  if (
    now - lastWiFiReconnectTime <
    WIFI_RECONNECT_INTERVAL_MS
  ) {
    return;
  }

  lastWiFiReconnectTime = now;

  Serial.println(
    "WiFi desconectado. Reconectando..."
  );

  WiFi.disconnect();

  WiFi.begin(
    ssid,
    password
  );
}