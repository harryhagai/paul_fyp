E-COMMERCE ROBOT ARM
WEBSITE ↔ ESP32 COMMUNICATION SPECIFICATION
Six-Axis Pick-and-Place Demonstration System
1. Purpose
This document defines how the e-commerce website communicates with the ESP32 robot controller. The website receives customer orders and tells the robot which parcel/location to pick. The ESP32 controls the robot arm, executes the programmed movement, places the parcel on the conveyor, and sends the operation status back to the website.
2. Communication Method
Communication type: HTTP REST API
Connection: Website Server → Wi-Fi → ESP32 → Robot Arm
The ESP32 connects to the same Wi-Fi network as the computer/server running the website. The website sends commands using HTTP requests. The ESP32 processes each command and returns an HTTP response.
HTTP is selected because it is simple to implement, easy to test, supported by ESP32, and suitable for the demonstration. MQTT is not required for the first version.
3. Overall Communication Flow
1. Customer places an order.
2. Website creates one queued robot command for each physical item in the order.
3. The queue worker sends the oldest queued command to the ESP32 using HTTP over Wi-Fi.
4. ESP32 validates the command.
5. ESP32 executes the pre-taught robot trajectory.
6. Robot picks the selected parcel.
7. Robot places the parcel on the conveyor.
8. ESP32 returns the operation status to the website.
9. Website sends the next queued pick cycle, if one exists.
10. Website updates the order after all of its pick cycles are complete.
4. Important Design Principle
The website does not control individual motors. It must not send step counts, servo angles, or low-level motor commands. Instead, it sends a high-level command such as PICK together with the order ID and parcel location.
Website responsibility: tell the robot WHAT to do.
ESP32 responsibility: decide HOW the robot moves.
5. Robot Locations
For the first demonstration, five parcel locations will be used. The system should also contain predefined HOME and CONVEYOR positions.
•	HOME
•	LOCATION 1
•	LOCATION 2
•	LOCATION 3
•	LOCATION 4
•	LOCATION 5
•	CONVEYOR
Additional locations can be taught later without changing the communication protocol.
6. Command Sent From Website to ESP32
HTTP method: POST
Example endpoint: /robot/command
Example JSON command:
{
  "command": "PICK",
  "order_id": "ORD1001",
  "location": 3
}
Meaning: Pick the parcel stored at location 3 for order ORD1001.
7. Commands Supported by ESP32
Command	Purpose
PICK	Moves to the selected location, picks the parcel, moves to the conveyor, releases it, and returns home.
HOME	Returns the robot to the predefined home position.
STOP	Stops the robot operation.
STATUS	Returns the current robot status.
8. ESP32 Response to Website
When a command is accepted:
{
  "status": "ACCEPTED",
  "order_id": "ORD1001",
  "location": 3
}
9. Robot Operation Status
Recommended status values:
•	IDLE
•	ACCEPTED
•	MOVING
•	PICKING
•	PLACING
•	COMPLETED
•	ERROR
•	STOPPED
Example progress responses:
{ "status": "MOVING", "order_id": "ORD1001" }
{ "status": "PICKING", "order_id": "ORD1001" }
{ "status": "PLACING", "order_id": "ORD1001" }
{ "status": "COMPLETED", "order_id": "ORD1001", "location": 3 }
10. Error Feedback
If an operation fails, the ESP32 should report an error.
{
  "status": "ERROR",
  "order_id": "ORD1001",
  "error": "LOCATION_NOT_AVAILABLE"
}
Possible error values:
•	INVALID_COMMAND
•	INVALID_LOCATION
•	ROBOT_BUSY
•	EMERGENCY_STOP
•	MOVEMENT_ERROR
•	PICK_FAILED
11. Complete Operation Sequence
1. Customer places an order on the website.
2. Website determines the parcel's stored location.
3. Website adds the PICK command to its FIFO queue.
4. The queue worker sends the command when the robot has no active operation.
5. ESP32 validates the command and confirms ACCEPTED.
6. ESP32 replays the stored trajectory for the selected location.
7. Robot picks the parcel.
8. Robot moves to the conveyor and releases the parcel.
9. Robot returns to HOME.
10. ESP32 reports COMPLETED.
11. Website processes the next queued cycle or completes the order.
12. Teach Pendant Communication
The Android teach-pendant application is used during the teaching/setup stage. The Android application communicates with the ESP32 through Bluetooth.
Teaching flow:
1.	Android application connects to ESP32 using Bluetooth.
2.	Operator manually moves the robot.
3.	Required positions are saved.
4.	Positions such as HOME, LOCATION 1–5, and CONVEYOR are stored.
5.	The stored positions are later used for automatic operation.
13. Automatic Operation Communication
After teaching is completed, normal automatic operation uses Wi-Fi and HTTP. The Android application is not required during normal order execution.
Teaching: Android App → Bluetooth → ESP32 → Robot
Automatic operation: Website → Wi-Fi/HTTP → ESP32 → Robot
14. Five-Parcel Demonstration
The demonstration will use five parcel locations. The website identifies the location associated with the ordered product and sends only the required location number to the ESP32.
Example:
6.	Customer orders Product 4.
7.	Website identifies LOCATION 4.
8.	Website sends PICK + LOCATION 4.
9.	ESP32 executes the stored LOCATION 4 trajectory.
10.	Robot picks the parcel and places it on the conveyor.
11.	ESP32 sends COMPLETED.
12.	Website updates the order.
15. Expansion
The communication protocol is not limited to five parcels. Additional locations can be added by teaching and storing new robot positions.
Example command for a future location:
{ "command": "PICK", "order_id": "ORD1001", "location": 12 }
The same communication protocol can therefore support LOCATION 1 through LOCATION 50, 100, or more, subject to the robot's physical storage capacity.
16. Responsibilities
Website Developer
•	Customer order interface
•	Product and location database
•	Order ID generation
•	Robot command generation
•	FIFO queue and multi-item pick-cycle management
•	HTTP POST communication
•	JSON request creation
•	Receiving ESP32 responses
•	Updating order status
ESP32 / Robot Developer
•	Wi-Fi connection
•	HTTP API/server
•	JSON command parsing
•	Command validation
•	Robot availability checking
•	Stored trajectory management
•	Three stepper motor control
•	Three servo motor control
•	Gripper control
•	Conveyor interface
•	Status and error feedback
17. Final Interface Agreement
Website → ESP32
HTTP POST /robot/command
{
  "command": "PICK",
  "order_id": "ORD1001",
  "location": 3
}
ESP32 → Website
{
  "status": "COMPLETED",
  "order_id": "ORD1001",
  "location": 3
}
18. One-Sentence System Agreement
The website sends a high-level PICK command containing the order ID and parcel location to the ESP32 through HTTP over Wi-Fi; the ESP32 executes the pre-taught trajectory and returns the robot's status to the website as JSON.
19. Implementation Note
For the demonstration, the website and ESP32 should be connected to the same local Wi-Fi network. The ESP32 can expose a local HTTP endpoint, and the website can send commands to the ESP32's local IP address. For a later production version, authentication, HTTPS, a central backend/API server, and multiple-robot management can be added.
