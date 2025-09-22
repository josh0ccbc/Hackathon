#define POT_PIN 34
int ledPins[] = {4, 5, 23, 13, 14, 15, 16, 17, 18, 19}; // 5 LEDs
int numLeds = 10;

void setup() {
  for (int i = 0; i < numLeds; i++) {
    pinMode(ledPins[i], OUTPUT);
  }
}

void loop() {
  int potValue = analogRead(POT_PIN); // 0–4095
  int level = map(potValue, 0, 4095, 0, numLeds); // map to 0–5

  for (int i = 0; i < numLeds; i++) {
    if (i < level) {
      digitalWrite(ledPins[i], HIGH); // LED ON
    } else {
      digitalWrite(ledPins[i], LOW);  // LED OFF
    }
  }

  delay(50);
}
