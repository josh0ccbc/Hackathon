#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <SPI.h>
#include <MFRC522.h>

// ================= OLED Setup =================
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 32   
#define OLED_RESET    -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// ================= Button Pins =================
#define UP_BTN     32
#define DOWN_BTN   33
#define CANCEL_BTN 25
#define OK_BTN     26

// ================= RFID Setup =================
#define SS_PIN     5
#define RST_PIN    4
MFRC522 rfid(SS_PIN, RST_PIN);

// ================= Ultrasonic Sensor =================
#define TRIG_PIN   27
#define ECHO_PIN   14

// ================= WiFi Credentials =================
const char* ssid = "MERCUSYS_7846";
const char* password = "52262944";

// ================= Server Endpoint =================
const char* serverURL = "http://192.168.1.102/barbershop/hackathon/walkin_queue.php";

// ================= Barber List =================
String barbers[] = {"REINER", "RALPH", "LAWRENCE"};
int barber_id;
int selectedBarber = 0;
bool confirmed = false;

// ================= Helper: Show message on OLED =================
void showMessage(String msg, int wait = 2000) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 10);
  display.println(msg);
  display.display();
  delay(wait);
}

// ================= Setup Functions =================
void setupOLED() {
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("OLED init failed!");
    while (true);
  }
  display.clearDisplay();
  display.display();
}

void setupButtons() {
  pinMode(UP_BTN, INPUT_PULLUP);
  pinMode(DOWN_BTN, INPUT_PULLUP);
  pinMode(CANCEL_BTN, INPUT_PULLUP);
  pinMode(OK_BTN, INPUT_PULLUP);
}

void setupRFID() {
  SPI.begin();
  rfid.PCD_Init();
}

void setupUltrasonic() {
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
}

void setupWiFi() {
  Serial.print("Connecting to WiFi");
  WiFi.begin(ssid, password);
  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 15000) {
    delay(500);
    Serial.print(".");
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
  } else {
    Serial.println("\nWiFi Failed. Running offline mode...");
  }
}

// ================= Ultrasonic =================
float getDistanceCM() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH, 30000);  // 30ms timeout
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

// ================= Barber Helper =================
int getBarberID(String barber) {
  if (barber == "REINER")   return 2;
  if (barber == "RALPH")    return 3;
  if (barber == "LAWRENCE") return 1;
  return 0;
}

// ================= UI Screens =================
void welcomeScreen() {
  showMessage("WELCOME TO KALBO!", 3000);
}

void selectBarber() {
  while (true) {
    display.clearDisplay();
    display.setTextSize(1);
    display.setCursor(0, 0);
    display.println("SELECT A BARBER:");

    for (int i = 0; i < 3; i++) {
      display.setCursor(0, 10 + i * 8);
      display.print(i == selectedBarber ? "> " : "  ");
      display.println(barbers[i]);
    }
    display.display();

    if (digitalRead(UP_BTN) == LOW) {
      selectedBarber = (selectedBarber - 1 + 3) % 3;
      delay(200);
    }
    if (digitalRead(DOWN_BTN) == LOW) {
      selectedBarber = (selectedBarber + 1) % 3;
      delay(200);
    }
    if (digitalRead(OK_BTN) == LOW) {
      barber_id = getBarberID(barbers[selectedBarber]);
      delay(200);
      break;
    }
  }
}

void confirmSelection() {
  int choice = 0;
  while (true) {
    display.clearDisplay();
    display.setCursor(0, 0);
    display.println("ARE YOU SURE?");
    display.setCursor(0, 10);
    display.println(choice == 0 ? "> YES" : "  YES");
    display.setCursor(0, 20);
    display.println(choice == 1 ? "> NO" : "  NO");
    display.display();

    if (digitalRead(UP_BTN) == LOW || digitalRead(DOWN_BTN) == LOW) {
      choice = 1 - choice;
      delay(200);
    }
    if (digitalRead(OK_BTN) == LOW) {
      confirmed = (choice == 0);
      delay(200);
      break;
    }
    if (digitalRead(CANCEL_BTN) == LOW) {
      confirmed = false;
      delay(200);
      break;
    }
  }
}

// ================= Server Communication =================
void sendToServer(String type, String value) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/json");

    String payload = "{\"type\":\"" + type + "\",\"value\":\"" + value + "\"}";
    int httpResponseCode = http.POST(payload);

    if (httpResponseCode > 0) {
      String response = http.getString();
      showMessage(response, 5000);
    } else {
      Serial.println("Error sending to server");
    }
    http.end();
  } else {
    Serial.println("WiFi not connected - data not sent");
  }
}

// ================= Main Setup =================
void setup() {
  Serial.begin(115200);
  setupOLED();
  setupButtons();
  setupRFID();
  setupUltrasonic();
  setupWiFi();
}

// ================= Main Loop =================
void loop() {
  float distance = getDistanceCM();
  if (distance <= 25.0) {
    display.ssd1306_command(SSD1306_DISPLAYON);  // Turn on OLED

    // RFID Flow
    if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
      String uid = "";
      for (byte i = 0; i < rfid.uid.size; i++) {
        uid += String(rfid.uid.uidByte[i], HEX);
      }
      sendToServer("RFID", uid);
      rfid.PICC_HaltA();
      rfid.PCD_StopCrypto1();
      delay(3000);
      return;
    }

    // Walk-in Flow
    welcomeScreen();
    selectBarber();
    confirmSelection();
    if (confirmed) {
      sendToServer("WALKIN", barbers[selectedBarber]);
      confirmed = false;
    }

  } else {
    display.clearDisplay();
    display.display();
    display.ssd1306_command(SSD1306_DISPLAYOFF);  // Power saving
  }

  delay(500);
}
