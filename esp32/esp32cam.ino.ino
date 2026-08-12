#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <ESPAsyncWebServer.h>

// =====================================================
// WIFI
// =====================================================

const char* ssid = "iPhone de Kevin";
const char* password = "kevin123";

// =====================================================
// ESP32-CAM AI THINKER
// =====================================================

#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27

#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5

#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

// LED flash integrado
#define FLASH_LED_PIN      4

// =====================================================
// SERVIDOR
// =====================================================

AsyncWebServer server(80);

// =====================================================
// PARÁMETROS
// =====================================================

const unsigned long WIFI_RECONNECT_INTERVAL_MS = 5000;

unsigned long lastWiFiReconnectTime = 0;

// =====================================================
// PROTOTIPOS
// =====================================================

bool iniciarCamara();

camera_fb_t* obtenerFrameActual();

bool enviarImagen(
  const String& serverURL,
  const String& fileName,
  camera_fb_t* fb
);

String urlEncode(
  const String& texto
);

void reconnectWiFi();

// =====================================================
// SETUP
// =====================================================

void setup() {
  Serial.begin(115200);

  pinMode(
    FLASH_LED_PIN,
    OUTPUT
  );

  // Mantener el flash apagado.
  digitalWrite(
    FLASH_LED_PIN,
    LOW
  );

  if (!iniciarCamara()) {
    Serial.println(
      "Error: no se pudo iniciar la cámara."
    );

    return;
  }

  WiFi.mode(WIFI_STA);

  Serial.print(
    "Conectando ESP32-CAM a WiFi"
  );

  WiFi.begin(
    ssid,
    password
  );

  while (
    WiFi.status() != WL_CONNECTED
  ) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println(
    "WiFi conectado."
  );

  Serial.print(
    "IP ESP32-CAM: "
  );

  Serial.println(
    WiFi.localIP()
  );

  // ===================================================
  // ENDPOINT /capture
  // ===================================================

  server.on(
    "/capture",
    HTTP_GET,
    [](AsyncWebServerRequest* request) {

      if (
        WiFi.status() != WL_CONNECTED
      ) {
        request->send(
          503,
          "text/plain",
          "NETWORK_ERROR"
        );

        return;
      }

      if (
        !request->hasParam("name") ||
        !request->hasParam("server")
      ) {
        request->send(
          400,
          "text/plain",
          "PARAM_ERROR"
        );

        return;
      }

      String fileName =
        request
          ->getParam("name")
          ->value();

      String serverURL =
        request
          ->getParam("server")
          ->value();

      fileName.trim();
      serverURL.trim();

      if (
        fileName.length() == 0 ||
        serverURL.length() == 0
      ) {
        request->send(
          400,
          "text/plain",
          "PARAM_ERROR"
        );

        return;
      }

      // Limpiar caracteres no permitidos.
      fileName.replace("/", "");
      fileName.replace("\\", "");
      fileName.replace("..", "");

      Serial.println();
      Serial.println(
        "=============================="
      );

      Serial.println(
        "Solicitud de captura recibida"
      );

      Serial.print(
        "Nombre de archivo: "
      );

      Serial.println(fileName);

      Serial.print(
        "Servidor de destino: "
      );

      Serial.println(serverURL);

      /*
       * IMPORTANTE:
       * Capturar sin flash.
       *
       * Las matrículas reflectivas pueden quedar
       * blancas o sobreexpuestas si se enciende
       * el LED integrado.
       */
      digitalWrite(
        FLASH_LED_PIN,
        LOW
      );

      camera_fb_t* fb =
        obtenerFrameActual();

      if (!fb) {
        Serial.println(
          "Error al obtener la imagen."
        );

        request->send(
          500,
          "text/plain",
          "ERROR_CAPTURE"
        );

        return;
      }

      Serial.print(
        "Tamaño de imagen: "
      );

      Serial.print(fb->len);

      Serial.println(
        " bytes"
      );

      bool envioCorrecto =
        enviarImagen(
          serverURL,
          fileName,
          fb
        );

      esp_camera_fb_return(fb);

      if (envioCorrecto) {
        Serial.println(
          "Imagen enviada correctamente."
        );

        request->send(
          200,
          "text/plain",
          "OK"
        );
      }
      else {
        Serial.println(
          "Error al enviar la imagen."
        );

        request->send(
          500,
          "text/plain",
          "ERROR_UPLOAD"
        );
      }

      Serial.println(
        "=============================="
      );
    }
  );

  // ===================================================
  // ENDPOINT /status
  // ===================================================

  server.on(
    "/status",
    HTTP_GET,
    [](AsyncWebServerRequest* request) {

      String respuesta =
        "ESP32_CAM_OK\nIP=" +
        WiFi.localIP().toString();

      request->send(
        200,
        "text/plain",
        respuesta
      );
    }
  );

  server.begin();

  Serial.println(
    "Servidor ESP32-CAM iniciado."
  );

  Serial.println(
    "Endpoint disponible: /capture"
  );
}

// =====================================================
// LOOP
// =====================================================

void loop() {
  reconnectWiFi();

  delay(20);
}

// =====================================================
// INICIAR CÁMARA
// =====================================================

bool iniciarCamara() {
  camera_config_t config;

  config.ledc_channel =
    LEDC_CHANNEL_0;

  config.ledc_timer =
    LEDC_TIMER_0;

  config.pin_d0 =
    Y2_GPIO_NUM;

  config.pin_d1 =
    Y3_GPIO_NUM;

  config.pin_d2 =
    Y4_GPIO_NUM;

  config.pin_d3 =
    Y5_GPIO_NUM;

  config.pin_d4 =
    Y6_GPIO_NUM;

  config.pin_d5 =
    Y7_GPIO_NUM;

  config.pin_d6 =
    Y8_GPIO_NUM;

  config.pin_d7 =
    Y9_GPIO_NUM;

  config.pin_xclk =
    XCLK_GPIO_NUM;

  config.pin_pclk =
    PCLK_GPIO_NUM;

  config.pin_vsync =
    VSYNC_GPIO_NUM;

  config.pin_href =
    HREF_GPIO_NUM;

  config.pin_sccb_sda =
    SIOD_GPIO_NUM;

  config.pin_sccb_scl =
    SIOC_GPIO_NUM;

  config.pin_pwdn =
    PWDN_GPIO_NUM;

  config.pin_reset =
    RESET_GPIO_NUM;

  config.xclk_freq_hz =
    20000000;

  config.pixel_format =
    PIXFORMAT_JPEG;

  if (psramFound()) {
    config.frame_size =
      FRAMESIZE_VGA;

    config.jpeg_quality =
      12;

    config.fb_count =
      2;

    config.grab_mode =
      CAMERA_GRAB_LATEST;

    config.fb_location =
      CAMERA_FB_IN_PSRAM;
  }
  else {
    config.frame_size =
      FRAMESIZE_QVGA;

    config.jpeg_quality =
      15;

    config.fb_count =
      1;

    config.grab_mode =
      CAMERA_GRAB_WHEN_EMPTY;

    config.fb_location =
      CAMERA_FB_IN_DRAM;
  }

  esp_err_t error =
    esp_camera_init(&config);

  if (error != ESP_OK) {
    Serial.print(
      "Error inicializando cámara: 0x"
    );

    Serial.println(
      error,
      HEX
    );

    return false;
  }

  sensor_t* sensor =
    esp_camera_sensor_get();

  if (sensor != nullptr) {
    sensor->set_brightness(
      sensor,
      0
    );

    sensor->set_contrast(
      sensor,
      1
    );

    sensor->set_saturation(
      sensor,
      0
    );

    sensor->set_sharpness(
      sensor,
      1
    );

    sensor->set_denoise(
      sensor,
      1
    );

    sensor->set_whitebal(
      sensor,
      1
    );

    sensor->set_awb_gain(
      sensor,
      1
    );

    sensor->set_exposure_ctrl(
      sensor,
      1
    );

    sensor->set_gain_ctrl(
      sensor,
      1
    );
  }

  Serial.println(
    "Cámara inicializada correctamente."
  );

  return true;
}

// =====================================================
// OBTENER IMAGEN ACTUAL
// =====================================================

camera_fb_t* obtenerFrameActual() {
  /*
   * Descartar el primer frame para evitar
   * utilizar una imagen anterior del buffer.
   */

  camera_fb_t* frameAnterior =
    esp_camera_fb_get();

  if (frameAnterior) {
    esp_camera_fb_return(
      frameAnterior
    );
  }

  // Dar tiempo para actualizar la imagen.
  delay(350);

  camera_fb_t* frameActual =
    esp_camera_fb_get();

  return frameActual;
}

// =====================================================
// ENVIAR IMAGEN
// =====================================================

bool enviarImagen(
  const String& serverURL,
  const String& fileName,
  camera_fb_t* fb
) {
  if (
    !fb ||
    fb->len == 0
  ) {
    return false;
  }

  HTTPClient http;

  String urlFinal =
    serverURL +
    "?name=" +
    urlEncode(fileName);

  Serial.print(
    "Enviando imagen a: "
  );

  Serial.println(urlFinal);

  http.setReuse(false);

  http.setConnectTimeout(
    10000
  );

  http.setTimeout(
    30000
  );

  if (!http.begin(urlFinal)) {
    Serial.println(
      "No se pudo iniciar la conexión HTTP."
    );

    return false;
  }

  http.addHeader(
    "Content-Type",
    "image/jpeg"
  );

  http.addHeader(
    "X-ESP32-CAM",
    "AI-THINKER"
  );

  int code =
    http.POST(
      fb->buf,
      fb->len
    );

  String respuesta = "";

  if (code > 0) {
    respuesta =
      http.getString();

    respuesta.trim();
  }

  Serial.print(
    "Código HTTP: "
  );

  Serial.println(code);

  Serial.print(
    "Respuesta del servidor: "
  );

  Serial.println(respuesta);

  http.end();

  return (
    code >= 200 &&
    code < 300 &&
    respuesta == "OK"
  );
}

// =====================================================
// CODIFICAR URL
// =====================================================

String urlEncode(
  const String& texto
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
    }
    else {
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
// RECONECTAR WIFI
// =====================================================

void reconnectWiFi() {
  if (
    WiFi.status() ==
    WL_CONNECTED
  ) {
    return;
  }

  unsigned long ahora =
    millis();

  if (
    ahora -
    lastWiFiReconnectTime <
    WIFI_RECONNECT_INTERVAL_MS
  ) {
    return;
  }

  lastWiFiReconnectTime =
    ahora;

  Serial.println(
    "WiFi desconectado. Reconectando..."
  );

  WiFi.disconnect();

  WiFi.begin(
    ssid,
    password
  );
}