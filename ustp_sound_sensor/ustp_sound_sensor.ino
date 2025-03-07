#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// WiFi credentials
const char* ssid = "POCO X6 5G";
const char* password = "mynikkuh";

// Analog pin for microphone sensor
#define sensorPin A0 

// GPIO pin for Red LED
#define ledPin 4 // D2 corresponds to GPIO4 on ESP8266

// Noise threshold
const int noiseThreshold = 500; // Adjust based on your sensor

void setup() {
  Serial.begin(9600);

  // Initialize LED pin as output
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW); // Start with LED off

  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.println("");
  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected.");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  int sensorValue = analogRead(sensorPin); // Read microphone sensor value
  Serial.print("Sensor Value: ");
  Serial.println(sensorValue);

  if (sensorValue > noiseThreshold) { // Check if noise exceeds threshold
    digitalWrite(ledPin, HIGH); // Turn on LED
    sendToServer(sensorValue); // Trigger sending to PHP
    delay(1000); // Avoid spamming the server
  } else {
    digitalWrite(ledPin, LOW); // Turn off LED
  }

  delay(500); // Add a delay for stability
}

// Function to send data to PHP server
void sendToServer(int noiseValue) {
  if (WiFi.status() == WL_CONNECTED) { // Ensure WiFi is connected
    WiFiClient client; // Create a WiFi client
    HTTPClient http;
    String serverPath = "http://192.168.104.242/endpoint.php"; // Replace with your PHP URL

    http.begin(client, serverPath); // Initialize HTTP client with WiFiClient and URL
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Set content type

    // Data to send
    String httpRequestData = "noiseLevel=" + String(noiseValue);
    int httpResponseCode = http.POST(httpRequestData); // Send POST request

    // Debug response
    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      Serial.print("Response: ");
      Serial.println(http.getString());
    } else {
      Serial.print("Error on sending POST: ");
      Serial.println(http.errorToString(httpResponseCode).c_str());
    }

    http.end(); // End HTTP connection
  } else {
    Serial.println("WiFi disconnected");
  }
}
